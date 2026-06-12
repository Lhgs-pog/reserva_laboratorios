"use client";

import { useEffect, useState } from "react";
import { createClient, type Agendamento } from "@/lib/supabase";
import { PainelLayout, StatCard, StatusBadge, LoadingState, formatDate } from "@/components/PainelLayout";

export default function CoordenadorPage() {
  const [agendamentos, setAgendamentos] = useState<Agendamento[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    createClient()
      .from("agendamentos")
      .select("*, laboratorios(nome), disciplinas(nome), usuarios(nome, email)")
      .order("data_reserva", { ascending: false })
      .then(({ data }) => {
        setAgendamentos((data as Agendamento[]) ?? []);
        setLoading(false);
      });
  }, []);

  async function atualizarStatus(id: number, status: "aprovado" | "rejeitado") {
    const supabase = createClient();
    await supabase.from("agendamentos").update({ status }).eq("id", id);
    setAgendamentos((prev) => prev.map((a) => (a.id === id ? { ...a, status } : a)));
  }

  const pendentes = agendamentos.filter((a) => a.status === "pendente").length;

  return (
    <PainelLayout titulo="Coordenador" usuario="Administrador" perfil="Coordenador">
      {loading ? (
        <LoadingState />
      ) : (
        <>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <StatCard label="Total de reservas" value={agendamentos.length} />
            <StatCard label="Aguardando aprovação" value={pendentes} color="text-yellow-600" />
            <StatCard
              label="Aprovadas"
              value={agendamentos.filter((a) => a.status === "aprovado").length}
              color="text-green-600"
            />
          </div>

          <div className="bg-white shadow-sm rounded-lg overflow-hidden">
            <div className="px-4 py-3 border-b font-semibold">Gestão de reservas</div>
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="text-left p-3">Data</th>
                    <th className="text-left p-3">Professor</th>
                    <th className="text-left p-3">Lab</th>
                    <th className="text-left p-3">Disciplina</th>
                    <th className="text-left p-3">Status</th>
                    <th className="text-left p-3">Ações</th>
                  </tr>
                </thead>
                <tbody>
                  {agendamentos.map((a) => (
                    <tr key={a.id} className="border-t hover:bg-gray-50">
                      <td className="p-3">{formatDate(a.data_reserva)}</td>
                      <td className="p-3">{a.usuarios?.nome ?? "—"}</td>
                      <td className="p-3">{a.laboratorios?.nome ?? "—"}</td>
                      <td className="p-3">{a.disciplinas?.nome ?? "—"}</td>
                      <td className="p-3"><StatusBadge status={a.status} /></td>
                      <td className="p-3 space-x-2">
                        {a.status === "pendente" && (
                          <>
                            <button
                              onClick={() => atualizarStatus(a.id, "aprovado")}
                              className="text-xs px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700"
                            >
                              Aprovar
                            </button>
                            <button
                              onClick={() => atualizarStatus(a.id, "rejeitado")}
                              className="text-xs px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700"
                            >
                              Rejeitar
                            </button>
                          </>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </>
      )}
    </PainelLayout>
  );
}
