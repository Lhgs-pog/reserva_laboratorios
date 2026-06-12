import Link from "next/link";

const chamados = [
  { titulo: "Projetor — Lab. Info 01", status: "Aberto", cor: "danger" },
  { titulo: "Rede Wi-Fi — Lab. Redes", status: "Resolvido", cor: "success" },
  { titulo: "Mouse quebrado — Lab. Info 02", status: "Resolvido", cor: "success" },
];

export default function SuportePage() {
  return (
    <>
      <nav className="navbar bg-white shadow-sm px-4">
        <span className="navbar-brand fw-bold text-uniceplac">LabHub — Suporte Técnico</span>
        <Link href="/" className="btn btn-sm btn-outline-secondary ms-auto">
          Sair
        </Link>
      </nav>

      <div className="container py-4">
        <div className="alert alert-danger">
          <i className="bi bi-exclamation-octagon me-2" />
          <strong>SOS ativo:</strong> Lab. Informática 01 — Projetor não liga (Prof. Ana Silva)
        </div>

        <div className="card shadow-sm">
          <div className="card-header fw-bold">Chamados recentes</div>
          <ul className="list-group list-group-flush">
            {chamados.map((c) => (
              <li
                key={c.titulo}
                className="list-group-item d-flex justify-content-between align-items-center"
              >
                <span>
                  <i className={`bi bi-circle-fill text-${c.cor} me-2 small`} />
                  {c.titulo}
                </span>
                <span className={`badge bg-${c.cor}`}>{c.status}</span>
              </li>
            ))}
          </ul>
        </div>
      </div>
    </>
  );
}
