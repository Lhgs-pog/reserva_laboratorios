import Link from "next/link";

const reservas = [
  {
    data: "12/06/2026",
    lab: "Lab. Informática 01",
    turno: "Noturno",
    periodo: "1º Horário",
    disciplina: "Programação Web",
    status: "Aprovado",
    badge: "badge-ok",
  },
  {
    data: "14/06/2026",
    lab: "Lab. Redes",
    turno: "Noturno",
    periodo: "2º Horário",
    disciplina: "Redes de Computadores",
    status: "Pendente",
    badge: "bg-warning text-dark",
  },
  {
    data: "16/06/2026",
    lab: "Lab. Informática 02",
    turno: "Noturno",
    periodo: "1º e 2º",
    disciplina: "Banco de Dados",
    status: "Aprovado",
    badge: "badge-ok",
  },
];

export default function ProfessorPage() {
  return (
    <>
      <nav className="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div className="container-fluid px-4">
          <Link className="navbar-brand navbar-brand-uniceplac" href="/">
            <i className="bi bi-building me-2" />
            LabHub UNICEPLAC
          </Link>
          <span className="text-muted small me-3">
            <i className="bi bi-person-circle me-1" />
            Prof. Ana Silva
          </span>
          <Link href="/" className="btn btn-sm btn-outline-secondary">
            Sair
          </Link>
        </div>
      </nav>

      <div className="container-fluid">
        <div className="row">
          <div className="col-md-3 col-lg-2 sidebar-uniceplac py-3">
            <a href="#" className="active">
              <i className="bi bi-calendar3 me-2" />
              Calendário de Reservas
            </a>
            <a href="#">
              <i className="bi bi-plus-circle me-2" />
              Nova Reserva
            </a>
            <a href="#">
              <i className="bi bi-key me-2" />
              Controle de Chaves
            </a>
            <a href="#">
              <i className="bi bi-bell me-2" />
              Chamados SOS
            </a>
            <a href="#">
              <i className="bi bi-person me-2" />
              Meu Perfil
            </a>
          </div>

          <div className="col-md-9 col-lg-10 p-4">
            <div className="alert alert-success border-0 shadow-sm">
              <i className="bi bi-check-circle me-2" />
              Bem-vindo, <strong>Prof. Ana Silva</strong>! Sistema de reservas de laboratórios.
            </div>

            <div className="row g-3 mb-4">
              <div className="col-md-4">
                <div className="card card-top shadow-sm">
                  <div className="card-body">
                    <h6 className="text-muted">Reservas aprovadas</h6>
                    <h3 className="text-success mb-0">5</h3>
                  </div>
                </div>
              </div>
              <div className="col-md-4">
                <div className="card card-top shadow-sm">
                  <div className="card-body">
                    <h6 className="text-muted">Pendentes</h6>
                    <h3 className="text-warning mb-0">2</h3>
                  </div>
                </div>
              </div>
              <div className="col-md-4">
                <div className="card card-top shadow-sm">
                  <div className="card-body">
                    <h6 className="text-muted">Labs disponíveis hoje</h6>
                    <h3 className="mb-0 text-uniceplac">3</h3>
                  </div>
                </div>
              </div>
            </div>

            <div className="card shadow-sm">
              <div className="card-header bg-white fw-bold">
                <i className="bi bi-calendar-week me-2" />
                Minhas reservas — Junho 2026
              </div>
              <div className="table-responsive">
                <table className="table table-hover mb-0">
                  <thead className="table-light">
                    <tr>
                      <th>Data</th>
                      <th>Laboratório</th>
                      <th>Turno</th>
                      <th>Período</th>
                      <th>Disciplina</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    {reservas.map((r) => (
                      <tr key={r.data + r.lab}>
                        <td>{r.data}</td>
                        <td>{r.lab}</td>
                        <td>{r.turno}</td>
                        <td>{r.periodo}</td>
                        <td>{r.disciplina}</td>
                        <td>
                          <span className={`badge ${r.badge}`}>{r.status}</span>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
