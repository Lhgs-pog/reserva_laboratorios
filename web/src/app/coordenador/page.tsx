"use client";

import Link from "next/link";
import { useCallback, useEffect, useState } from "react";
import { createClient, type Agendamento } from "@/lib/supabase";

function formatDate(iso: string) {
  return new Date(iso + "T12:00:00").toLocaleDateString("pt-BR", {
    day: "2-digit",
    month: "2-digit",
  });
}

export default function CoordenadorPage() {
  const [pendentes, setPendentes] = useState<Agendamento[]>([]);
  const [stats, setStats] = useState({
    labs: 0,
    pendentes: 0,
    professores: 0,
    sos: 0,
  });
  const [loading, setLoading] = useState(true);
  const [msg, setMsg] = useState("");
  const [processando, setProcessando] = useState<number | null>(null);

  const carregar = useCallback(async () => {
    const supabase = createClient();

    const [labs, usuarios, sos, agPendentes, agTodos] = await Promise.all([
      supabase.from("laboratorios").select("id", { count: "exact", head: true }),
      supabase.from("usuarios").select("id", { count: "exact", head: true }).eq("perfil", "professor"),
      supabase.from("chamados_suporte").select("id", { count: "exact", head: true }).eq("status", "pendente"),
      supabase
        .from("agendamentos")
        .select("*, laboratorios(nome), disciplinas(nome), usuarios(nome, email)")
        .eq("status", "pendente")
        .order("data_reserva", { ascending: true }),
      supabase.from("agendamentos").select("id", { count: "exact", head: true }).eq("status", "pendente"),
    ]);

    setPendentes((agPendentes.data as Agendamento[]) ?? []);
    setStats({
      labs: labs.count ?? 0,
      pendentes: agTodos.count ?? 0,
      professores: usuarios.count ?? 0,
      sos: sos.count ?? 0,
    });
    setLoading(false);
  }, []);

  useEffect(() => {
    carregar();
  }, [carregar]);

  async function atualizarStatus(id: number, status: "aprovado" | "rejeitado") {
    setProcessando(id);
    setMsg("");
    const supabase = createClient();
    const { error } = await supabase.from("agendamentos").update({ status }).eq("id", id);

    if (error) {
      setMsg("Erro ao atualizar reserva. Tente novamente.");
      setProcessando(null);
      return;
    }

    setMsg(status === "aprovado" ? "Reserva aprovada com sucesso!" : "Reserva recusada.");
    await carregar();
    setProcessando(null);
  }

  return (
    <>
      <nav className="navbar navbar-light bg-white shadow-sm">
        <div className="container-fluid px-4">
          <Link className="navbar-brand navbar-brand-uniceplac" href="/">
            LabHub — Coordenação
          </Link>
          <span className="small text-muted me-3">Coord. Carlos Mendes</span>
          <Link href="/" className="btn btn-sm btn-outline-secondary">
            Sair
          </Link>
        </div>
      </nav>

      <div className="container-fluid">
        <div className="row">
          <div className="col-md-3 sidebar-uniceplac py-3">
            <a href="#" className="active">
              <i className="bi bi-grid me-2" />
              Dashboard
            </a>
            <a href="#">
              <i className="bi bi-check2-square me-2" />
              Aprovar Reservas
            </a>
            <a href="#">
              <i className="bi bi-pc-display me-2" />
              Laboratórios
            </a>
            <a href="#">
              <i className="bi bi-book me-2" />
              Disciplinas
            </a>
            <a href="#">
              <i className="bi bi-calendar-range me-2" />
              Quadro de Horários
            </a>
            <a href="#">
              <i className="bi bi-bar-chart me-2" />
              Relatórios
            </a>
          </div>

          <div className="col-md-9 p-4">
            <h4 className="mb-4 text-uniceplac">Painel do Coordenador</h4>

            {msg && (
              <div className={`alert ${msg.includes("Erro") ? "alert-danger" : "alert-success"} py-2`}>
                {msg}
              </div>
            )}

            <div className="row g-3 mb-4">
              <div className="col-md-3">
                <div className="card shadow-sm p-3">
                  <small className="text-muted">Labs cadastrados</small>
                  <h4>{stats.labs}</h4>
                </div>
              </div>
              <div className="col-md-3">
                <div className="card shadow-sm p-3">
                  <small className="text-muted">Reservas pendentes</small>
                  <h4 className="text-warning">{stats.pendentes}</h4>
                </div>
              </div>
              <div className="col-md-3">
                <div className="card shadow-sm p-3">
                  <small className="text-muted">Professores ativos</small>
                  <h4>{stats.professores}</h4>
                </div>
              </div>
              <div className="col-md-3">
                <div className="card shadow-sm p-3">
                  <small className="text-muted">SOS abertos</small>
                  <h4 className="text-danger">{stats.sos}</h4>
                </div>
              </div>
            </div>

            <div className="card shadow-sm">
              <div className="card-header bg-white fw-bold">
                Solicitações aguardando aprovação
              </div>
              {loading ? (
                <div className="p-4 text-muted">Carregando solicitações...</div>
              ) : pendentes.length === 0 ? (
                <div className="p-4 text-muted">Nenhuma solicitação pendente no momento.</div>
              ) : (
                <table className="table mb-0">
                  <thead className="table-light">
                    <tr>
                      <th>Professor</th>
                      <th>Lab</th>
                      <th>Data</th>
                      <th>Turno</th>
                      <th>Ação</th>
                    </tr>
                  </thead>
                  <tbody>
                    {pendentes.map((s) => (
                      <tr key={s.id}>
                        <td>{s.usuarios?.nome ?? "—"}</td>
                        <td>{s.laboratorios?.nome ?? "—"}</td>
                        <td>{formatDate(s.data_reserva)}</td>
                        <td>{s.turno}</td>
                        <td>
                          <button
                            type="button"
                            className="btn btn-sm btn-success me-1"
                            disabled={processando === s.id}
                            onClick={() => atualizarStatus(s.id, "aprovado")}
                          >
                            {processando === s.id ? "..." : "Aprovar"}
                          </button>
                          <button
                            type="button"
                            className="btn btn-sm btn-outline-danger"
                            disabled={processando === s.id}
                            onClick={() => atualizarStatus(s.id, "rejeitado")}
                          >
                            Recusar
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              )}
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
