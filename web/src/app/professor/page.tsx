"use client";

import { useEffect, useState } from "react";
import { createClient, type Agendamento } from "@/lib/supabase";
import { PainelLayout, StatCard, StatusBadge, LoadingState, formatDate } from "@/components/PainelLayout";

export default function ProfessorPage() {
  const [agendamentos, setAgendamentos] = useState<Agendamento[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function load() {
      const supabase = createClient();
      const { data: usuario } = await supabase
        .from("usuarios")
        .select("id")
        .eq("email", "professor@uniceplac.edu.br")
        .single();

      let query = supabase
        .from("agendamentos")
        .select("*, laboratorios(nome), disciplinas(nome), usuarios(nome, email)")
        .order("data_reserva", { ascending: true });

      if (usuario?.id) {
        query = query.eq("id_professor", usuario.id);
      }

      const { data } = await query;
      setAgendamentos((data as Agendamento[]) ?? []);
      setLoading(false);
    }
    load();
  }, []);

  const aprovados = agendamentos.filter((a) => a.status === "aprovado").length;
  const pendentes = agendamentos.filter((a) => a.status === "pendente").length;

  return (
    <PainelLayout titulo="Professor" usuario="Prof. Ana Silva" perfil="Demo">
      {loading ? (
        <LoadingState />
      ) : (
        <>
          <div className="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 text-green-800">
            Bem-vindo, <strong>Prof. Ana Silva</strong>! Reservas sincronizadas com Supabase em tempo real.
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <StatCard label="Reservas aprovadas" value={aprovados} color="text-green-600" />
            <StatCard label="Pendentes" value={pendentes} color="text-yellow-600" />
            <StatCard label="Total" value={agendamentos.length} />
          </div>

          <div className="bg-white shadow-sm rounded-lg overflow-hidden">
            <div className="px-4 py-3 border-b font-semibold">Minhas reservas</div>
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="text-left p-3">Data</th>
                    <th className="text-left p-3">Laboratório</th>
                    <th className="text-left p-3">Turno</th>
                    <th className="text-left p-3">Período</th>
                    <th className="text-left p-3">Disciplina</th>
                    <th className="text-left p-3">Status</th>
                  </tr>
                </thead>
                <tbody>
                  {agendamentos.length === 0 ? (
                    <tr>
                      <td colSpan={6} className="p-6 text-center text-gray-500">
                        Nenhuma reserva encontrada.
                      </td>
                    </tr>
                  ) : (
                    agendamentos.map((a) => (
                      <tr key={a.id} className="border-t hover:bg-gray-50">
                        <td className="p-3">{formatDate(a.data_reserva)}</td>
                        <td className="p-3">{a.laboratorios?.nome ?? "—"}</td>
                        <td className="p-3">{a.turno}</td>
                        <td className="p-3">{a.periodo}</td>
                        <td className="p-3">{a.disciplinas?.nome ?? "—"}</td>
                        <td className="p-3"><StatusBadge status={a.status} /></td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </>
      )}
    </PainelLayout>
  );
}
