import Link from "next/link";

const solicitacoes = [
  {
    professor: "Prof. João",
    lab: "Lab. Informática 01",
    data: "15/06",
    turno: "Noturno",
  },
  {
    professor: "Prof. Maria",
    lab: "Lab. Química",
    data: "17/06",
    turno: "Vespertino",
  },
];

export default function CoordenadorPage() {
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

            <div className="row g-3 mb-4">
              <div className="col-md-3">
                <div className="card shadow-sm p-3">
                  <small className="text-muted">Labs cadastrados</small>
                  <h4>8</h4>
                </div>
              </div>
              <div className="col-md-3">
                <div className="card shadow-sm p-3">
                  <small className="text-muted">Reservas pendentes</small>
                  <h4 className="text-warning">4</h4>
                </div>
              </div>
              <div className="col-md-3">
                <div className="card shadow-sm p-3">
                  <small className="text-muted">Professores ativos</small>
                  <h4>24</h4>
                </div>
              </div>
              <div className="col-md-3">
                <div className="card shadow-sm p-3">
                  <small className="text-muted">SOS abertos</small>
                  <h4 className="text-danger">1</h4>
                </div>
              </div>
            </div>

            <div className="card shadow-sm">
              <div className="card-header bg-white fw-bold">
                Solicitações aguardando aprovação
              </div>
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
                  {solicitacoes.map((s) => (
                    <tr key={s.professor + s.data}>
                      <td>{s.professor}</td>
                      <td>{s.lab}</td>
                      <td>{s.data}</td>
                      <td>{s.turno}</td>
                      <td>
                        <button type="button" className="btn btn-sm btn-success me-1">
                          Aprovar
                        </button>
                        <button type="button" className="btn btn-sm btn-outline-danger">
                          Recusar
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}
