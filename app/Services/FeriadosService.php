<?php
namespace App\Services;

class FeriadosService
{
    private const API_URL = 'https://brasilapi.com.br/api/feriados/v1/';
    private const CACHE_TTL = 86400;

    /** @return array<int, array<string, mixed>> */
    public function eventosFullCalendar(int $anoInicio, int $anoFim): array
    {
        $eventos = [];
        $mapa = $this->mapaDatasEspeciaisRange($anoInicio, $anoFim);

        foreach ($mapa as $data => $info) {
            $tipo = $info['tipo'];
            $nome = $info['nome'];
            $prefixo = $tipo === 'facultativo' ? 'Ponto facultativo' : 'Feriado';
            $classe = $tipo === 'facultativo' ? 'apple-event-facultativo' : 'apple-event-feriado';
            $local = $tipo === 'facultativo'
                ? '<i class="bi bi-calendar-minus me-1"></i> Possível ponto facultativo (DF)'
                : '<i class="bi bi-calendar-x me-1"></i> Instituição fechada';

            if (!empty($info['escopo']) && $info['escopo'] === 'df') {
                $local = '<i class="bi bi-geo-alt me-1"></i> Distrito Federal — ' . strip_tags($local);
            }

            $eventos[] = [
                'title' => $prefixo . ': ' . $nome,
                'start' => $data,
                'allDay' => true,
                'className' => $classe,
                'extendedProps' => [
                    'local' => $local,
                    'tipoCalendario' => $tipo,
                    'escopo' => $info['escopo'] ?? 'nacional',
                ],
            ];
        }

        return $eventos;
    }

    /** @return array<string, array{tipo: string, nome: string, escopo?: string}> */
    public function mapaDatasEspeciais(?int $ano = null): array
    {
        $ano = $ano ?? (int) date('Y');
        return $this->mapaDatasEspeciaisRange($ano, $ano);
    }

    /** @return array<string, array{tipo: string, nome: string, escopo?: string}> */
    public function mapaDatasEspeciaisRange(int $anoInicio, int $anoFim): array
    {
        $mapa = [];

        for ($ano = $anoInicio; $ano <= $anoFim; $ano++) {
            foreach ($this->feriadosNacionais($ano) as $item) {
                $mapa[$item['date']] = [
                    'tipo' => 'feriado',
                    'nome' => $item['name'],
                    'escopo' => 'nacional',
                ];
            }

            foreach ($this->feriadosDistritais($ano) as $data => $nome) {
                if (!isset($mapa[$data])) {
                    $mapa[$data] = [
                        'tipo' => 'feriado',
                        'nome' => $nome,
                        'escopo' => 'df',
                    ];
                } elseif ($mapa[$data]['escopo'] === 'nacional') {
                    $mapa[$data]['nome'] .= ' / ' . $nome;
                }
            }

            foreach ($this->pontosFacultativosDf($ano) as $data => $nome) {
                if (isset($mapa[$data]) && $mapa[$data]['tipo'] === 'feriado') {
                    continue;
                }
                $mapa[$data] = [
                    'tipo' => 'facultativo',
                    'nome' => $nome,
                    'escopo' => 'df',
                ];
            }
        }

        ksort($mapa);
        return $mapa;
    }

    /** @return list<array{date: string, name: string}> */
    private function feriadosNacionais(int $ano): array
    {
        $cached = $this->lerCache($ano);
        if ($cached !== null) {
            return $cached;
        }

        $lista = $this->buscarApi($ano);
        if ($lista === null) {
            $lista = $this->fallbackNacionais($ano);
        } else {
            $this->gravarCache($ano, $lista);
        }

        return $lista;
    }

    /** @return array<string, string> */
    private function feriadosDistritais(int $ano): array
    {
        $cfg = $this->configDf();
        $out = [];
        foreach ($cfg['feriado_distrital'] ?? [] as $md => $nome) {
            $out[sprintf('%04d-%s', $ano, $md)] = $nome;
        }
        return $out;
    }

    /** @return array<string, string> */
    private function pontosFacultativosDf(int $ano): array
    {
        $cfg = $this->configDf();
        $out = [];

        foreach ($cfg['pontos_facultativos_fixos'] ?? [] as $md => $nome) {
            $out[sprintf('%04d-%s', $ano, $md)] = $nome;
        }

        $nacionais = $this->feriadosNacionais($ano);
        $carnaval = null;
        $corpus = null;
        foreach ($nacionais as $f) {
            $n = mb_strtolower($f['name'], 'UTF-8');
            if (str_contains($n, 'carnaval')) {
                $carnaval = $f['date'];
            }
            if (str_contains($n, 'corpus christi')) {
                $corpus = $f['date'];
            }
        }

        $rel = $cfg['pontos_facultativos_relativos'] ?? [];
        if ($carnaval) {
            $ts = strtotime($carnaval);
            if (isset($rel['carnaval_segunda'])) {
                $out[date('Y-m-d', strtotime('-1 day', $ts))] = $rel['carnaval_segunda'];
            }
            if (isset($rel['carnaval_quarta_cinzas'])) {
                $out[date('Y-m-d', strtotime('+1 day', $ts))] = $rel['carnaval_quarta_cinzas'];
            }
        }
        if ($corpus && isset($rel['corpus_emenda'])) {
            $out[date('Y-m-d', strtotime($corpus . ' +1 day'))] = $rel['corpus_emenda'];
        }

        return $out;
    }

    /** @return list<array{date: string, name: string}>|null */
    private function buscarApi(int $ano): ?array
    {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 4,
                'header' => "Accept: application/json\r\nUser-Agent: LabHub-UNICEPLAC/1.0\r\n",
            ],
        ]);

        $raw = @file_get_contents(self::API_URL . $ano, false, $ctx);
        if ($raw === false) {
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return null;
        }

        $lista = [];
        foreach ($data as $row) {
            if (empty($row['date']) || empty($row['name'])) {
                continue;
            }
            $lista[] = [
                'date' => $row['date'],
                'name' => $row['name'],
            ];
        }

        return $lista ?: null;
    }

    /** @return list<array{date: string, name: string}> */
    private function fallbackNacionais(int $ano): array
    {
        $base = [
            '01-01' => 'Confraternização Universal',
            '04-21' => 'Tiradentes',
            '05-01' => 'Dia do Trabalho',
            '09-07' => 'Independência do Brasil',
            '10-12' => 'Nossa Senhora Aparecida',
            '11-02' => 'Finados',
            '11-15' => 'Proclamação da República',
            '11-20' => 'Dia da Consciência Negra',
            '12-25' => 'Natal',
        ];

        $lista = [];
        foreach ($base as $md => $nome) {
            $lista[] = ['date' => $ano . '-' . $md, 'name' => $nome];
        }

        if ($ano === 2026) {
            $lista[] = ['date' => '2026-02-17', 'name' => 'Carnaval'];
            $lista[] = ['date' => '2026-04-03', 'name' => 'Sexta-feira Santa'];
            $lista[] = ['date' => '2026-06-04', 'name' => 'Corpus Christi'];
        }

        return $lista;
    }

    /** @return list<array{date: string, name: string}>|null */
    private function lerCache(int $ano): ?array
    {
        $path = $this->cachePath($ano);
        if (!is_file($path)) {
            return null;
        }
        if (filemtime($path) + self::CACHE_TTL < time()) {
            return null;
        }
        $data = json_decode((string) file_get_contents($path), true);
        return is_array($data) ? $data : null;
    }

    /** @param list<array{date: string, name: string}> $lista */
    private function gravarCache(int $ano, array $lista): void
    {
        $dir = dirname($this->cachePath($ano));
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($this->cachePath($ano), json_encode($lista, JSON_UNESCAPED_UNICODE));
    }

    private function cachePath(int $ano): string
    {
        return sys_get_temp_dir() . '/labhub-feriados-' . $ano . '.json';
    }

    /** @return array<string, mixed> */
    private function configDf(): array
    {
        static $cfg = null;
        if ($cfg === null) {
            $path = __DIR__ . '/../Config/feriados_df.php';
            $cfg = is_file($path) ? require $path : [];
        }
        return $cfg;
    }
}
