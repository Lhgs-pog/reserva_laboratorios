import { createBrowserClient } from "@supabase/ssr";

export function createClient() {
  return createBrowserClient(
    process.env.NEXT_PUBLIC_SUPABASE_URL!,
    process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!
  );
}

export type Agendamento = {
  id: number;
  data_reserva: string;
  turno: string;
  periodo: string;
  status: string;
  laboratorios: { nome: string } | null;
  disciplinas: { nome: string } | null;
  usuarios: { nome: string; email: string } | null;
};

export type Usuario = {
  id: number;
  nome: string;
  email: string;
  perfil: "coordenador" | "professor" | "suporte";
};

export type ChamadoSuporte = {
  id: number;
  professor_nome: string;
  laboratorio: string;
  mensagem: string;
  status: string;
  data_hora: string;
};
