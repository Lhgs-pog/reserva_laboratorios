import Image from "next/image";
import Link from "next/link";

const perfis = [
  { href: "/professor", label: "Professor", email: "professor@uniceplac.edu.br" },
  { href: "/coordenador", label: "Coordenador", email: "admin@uniceplac.edu.br" },
  { href: "/suporte", label: "Suporte", email: "suporte@uniceplac.edu.br" },
];

export default function HomePage() {
  return (
    <main className="min-h-screen flex items-center justify-center p-4">
      <span className="demo-badge fixed top-3 right-3 z-50">
        Demonstração online
      </span>

      <div className="w-full max-w-md bg-white shadow-xl card-uniceplac p-8 md:p-10">
        <div className="text-center mb-6">
          <Image
            src="/uniceplac2.png"
            alt="Logo UNICEPLAC"
            width={200}
            height={100}
            className="mx-auto mb-4 h-24 w-auto"
            priority
          />
          <h1 className="text-uniceplac font-bold text-sm tracking-wide">
            CENTRAL DE RESERVAS ACADÊMICAS
          </h1>
          <p className="text-sm text-gray-500 mt-1">
            LabHub UNICEPLAC — Sistema de reserva de laboratórios
          </p>
        </div>

        <div className="space-y-3">
          {perfis.map((p) => (
            <Link
              key={p.href}
              href={p.href}
              className="flex items-center justify-between w-full px-4 py-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition"
            >
              <div>
                <div className="font-semibold text-uniceplac">{p.label}</div>
                <div className="text-xs text-gray-500">{p.email}</div>
              </div>
              <span className="text-uniceplac">→</span>
            </Link>
          ))}
        </div>

        <div className="mt-6 p-4 bg-gray-50 rounded-lg text-sm text-gray-600">
          <strong className="text-uniceplac">Demo acadêmica</strong>
          <p className="mt-1">
            Dados em tempo real via Supabase (PostgreSQL). Deploy automático via
            GitHub → Vercel.
          </p>
        </div>
      </div>
    </main>
  );
}
