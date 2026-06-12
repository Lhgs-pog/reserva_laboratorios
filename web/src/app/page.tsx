"use client";

import Image from "next/image";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { FormEvent, useState } from "react";

function GoogleIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 48 48">
      <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
      <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
      <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z" />
      <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
    </svg>
  );
}

function rotaPorEmail(email: string): string {
  const e = email.toLowerCase();
  if (e.includes("admin") || e.includes("coordenador")) return "/coordenador";
  if (e.includes("suporte")) return "/suporte";
  return "/professor";
}

export default function LoginPage() {
  const router = useRouter();
  const [erro, setErro] = useState("");

  function handleSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    const email = String(form.get("email") ?? "").trim();
    const senha = String(form.get("senha") ?? "").trim();
    if (!email || !senha) {
      setErro("Informe e-mail e senha.");
      return;
    }
    router.push(rotaPorEmail(email));
  }

  return (
    <div className="d-flex justify-content-center align-items-center vh-100">
      <div className="container d-flex justify-content-center">
        <div className="card card-uniceplac shadow-lg" style={{ width: "28rem" }}>
          <div className="card-body p-4 p-md-5">
            <div className="text-center mb-4">
              <Image
                src="/uniceplac2.png"
                alt="Logo UNICEPLAC"
                width={200}
                height={100}
                className="mb-3"
                style={{ maxHeight: 100, width: "auto", height: "auto" }}
                priority
              />
              <h6 className="text-uniceplac fw-bold">CENTRAL DE RESERVAS ACADÊMICAS</h6>
              <p className="small text-muted mb-0">LabHub UNICEPLAC — Sistema de reserva de laboratórios</p>
            </div>

            {erro && (
              <div className="alert alert-danger py-2 small text-center">{erro}</div>
            )}

            <Link href="/professor" className="btn btn-google w-100 py-2 mb-2">
              <GoogleIcon />
              Entrar com e-mail institucional
            </Link>

            <div className="divider">OU</div>

            <form onSubmit={handleSubmit}>
              <div className="mb-3">
                <label htmlFor="email" className="form-label small fw-bold text-uniceplac">
                  E-mail
                </label>
                <input
                  type="email"
                  className="form-control"
                  name="email"
                  id="email"
                  required
                  placeholder="exemplo@uniceplac.edu.br"
                />
              </div>
              <div className="mb-4">
                <label htmlFor="senha" className="form-label small fw-bold text-uniceplac">
                  Senha
                </label>
                <input type="password" className="form-control" name="senha" id="senha" required />
              </div>
              <button type="submit" className="btn btn-uniceplac w-100 py-2">
                Acessar Sistema
              </button>
              <div className="text-center mt-4 pt-2">
                <p className="mb-1 text-muted" style={{ fontSize: "0.85em" }}>
                  Ainda não possui acesso?
                </p>
                <Link
                  href="/cadastro"
                  className="text-decoration-none fw-bold small"
                  style={{ color: "var(--amarelo-uniceplac)" }}
                >
                  Solicitar Cadastro
                </Link>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}
