"use client";

import { useEffect, useState } from "react";
import { createClient, type ChamadoSuporte } from "@/lib/supabase";
import { PainelLayout, StatCard, StatusBadge, LoadingState } from "@/components/PainelLayout";

export default function SuportePage() {
  const [chamados, setChamados] = useState<ChamadoSuporte[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    createClient()
      .from("chamados_suporte")
      .select("*")
      .order("data_hora", { ascending: false })
      .then(({ data }) => {
        setChamados((data as ChamadoSuporte[]) ?? []);
        setLoading(false);
      });
  }, []);

  async function resolver(id: number) {
    const supabase = createClient();
    await supabase.from("chamados_suporte").update({ status: "resolvido" }).eq("id", id);
    setChamados((prev) => prev.map((c) => (c.id === id ? { ...c, status: "resolvido" } : c)));
  }

  const pendentes = chamados.filter((c) => c.status === "pendente").length;

  return (
    <PainelLayout titulo="Suporte" usuario="Técnico Carlos" perfil="Suporte">
      {loading ? (
        <LoadingState />
      ) : (
        <>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <StatCard label="Chamados abertos" value={pendentes} color="text-yellow-600" />
            <StatCard label="Total" value={chamados.length} />
          </div>

          <div className="bg-white shadow-sm rounded-lg overflow-hidden">
            <div className="px-4 py-3 border-b font-semibold">Chamados SOS</div>
            {chamados.length === 0 ? (
              <div className="p-8 text-center text-gray-500">
                Nenhum chamado registrado. Dados carregados do Supabase em tempo real.
              </div>
            ) : (
              <div className="divide-y">
                {chamados.map((c) => (
                  <div key={c.id} className="p-4 hover:bg-gray-50">
                    <div className="flex justify-between items-start gap-4">
                      <div>
                        <div className="font-semibold">{c.professor_nome}</div>
                        <div className="text-sm text-gray-500">{c.laboratorio}</div>
                        <p className="mt-2 text-sm">{c.mensagem}</p>
                        <div className="text-xs text-gray-400 mt-1">
                          {new Date(c.data_hora).toLocaleString("pt-BR")}
                        </div>
                      </div>
                      <div className="flex flex-col items-end gap-2">
                        <StatusBadge status={c.status} />
                        {c.status === "pendente" && (
                          <button
                            onClick={() => resolver(c.id)}
                            className="text-xs px-3 py-1 btn-uniceplac rounded"
                          >
                            Resolver
                          </button>
                        )}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </>
      )}
    </PainelLayout>
  );
}
