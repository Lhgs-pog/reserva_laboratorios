"use client";

import Link from "next/link";
import Image from "next/image";

type Props = {
  titulo: string;
  usuario: string;
  perfil: string;
  children: React.ReactNode;
};

export function PainelLayout({ titulo, usuario, perfil, children }: Props) {
  return (
    <div className="min-h-screen">
      <nav className="bg-white shadow-sm border-b">
        <div className="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between">
          <Link href="/" className="flex items-center gap-2 font-bold text-uniceplac">
            <Image src="/uniceplac2.png" alt="" width={32} height={32} className="h-8 w-auto" />
            LabHub UNICEPLAC
          </Link>
          <div className="flex items-center gap-3 text-sm">
            <span className="demo-badge">{perfil}</span>
            <span className="text-gray-600 hidden sm:inline">{usuario}</span>
            <Link href="/" className="px-3 py-1 border rounded text-gray-600 hover:bg-gray-50">
              Sair
            </Link>
          </div>
        </div>
      </nav>

      <div className="max-w-7xl mx-auto flex">
        <aside className="sidebar w-56 shrink-0 hidden md:block py-4">
          <div className="px-4 text-white/70 text-xs uppercase mb-2">{titulo}</div>
          <a href="#" className="sidebar-link active">Dashboard</a>
          <a href="#" className="sidebar-link">Calendário</a>
          <a href="#" className="sidebar-link">Relatórios</a>
        </aside>
        <main className="flex-1 p-4 md:p-6">{children}</main>
      </div>
    </div>
  );
}

export function StatCard({ label, value, color }: { label: string; value: number | string; color?: string }) {
  return (
    <div className="bg-white shadow-sm card-top p-4">
      <div className="text-sm text-gray-500">{label}</div>
      <div className={`text-3xl font-bold mt-1 ${color ?? "text-uniceplac"}`}>{value}</div>
    </div>
  );
}

export function StatusBadge({ status }: { status: string }) {
  const map: Record<string, string> = {
    aprovado: "bg-green-100 text-green-800",
    pendente: "bg-yellow-100 text-yellow-800",
    rejeitado: "bg-red-100 text-red-800",
    resolvido: "bg-green-100 text-green-800",
  };
  return (
    <span className={`px-2 py-1 rounded text-xs font-medium capitalize ${map[status] ?? "bg-gray-100"}`}>
      {status}
    </span>
  );
}

export function LoadingState() {
  return (
    <div className="flex items-center justify-center py-20 text-gray-500">
      <div className="animate-pulse">Carregando dados do Supabase...</div>
    </div>
  );
}

export function formatDate(iso: string) {
  return new Date(iso + "T12:00:00").toLocaleDateString("pt-BR");
}
