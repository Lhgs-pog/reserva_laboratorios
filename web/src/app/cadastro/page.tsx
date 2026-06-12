"use client";

import Link from "next/link";
import { FormEvent } from "react";
import { useRouter } from "next/navigation";

export default function CadastroPage() {
  const router = useRouter();

  function handleSubmit(e: FormEvent) {
    e.preventDefault();
    alert("Cadastro enviado! Aguarde aprovação do coordenador.");
    router.push("/");
  }

  return (
    <div className="d-flex align-items-center justify-content-center vh-100">
      <div className="card card-uniceplac shadow-lg p-4" style={{ width: "28rem" }}>
        <h5 className="text-center mb-3 text-uniceplac">Solicitar Cadastro</h5>
        <form onSubmit={handleSubmit}>
          <div className="mb-3">
            <label className="form-label small fw-bold">Nome completo</label>
            <input className="form-control" name="nome" required placeholder="Seu nome" />
          </div>
          <div className="mb-3">
            <label className="form-label small fw-bold">E-mail institucional</label>
            <input
              type="email"
              className="form-control"
              name="email"
              required
              placeholder="nome@uniceplac.edu.br"
            />
          </div>
          <div className="mb-3">
            <label className="form-label small fw-bold">Perfil</label>
            <select className="form-select" name="perfil" defaultValue="professor">
              <option value="professor">Professor</option>
              <option value="suporte">Suporte</option>
            </select>
          </div>
          <button type="submit" className="btn btn-uniceplac w-100">
            Enviar solicitação
          </button>
          <Link href="/" className="d-block text-center mt-3 small">
            Voltar ao login
          </Link>
        </form>
      </div>
    </div>
  );
}
