import type { Metadata } from "next";
import "./globals.css";

export const metadata: Metadata = {
  title: "UNICEPLAC - Central de Reservas",
  description: "LabHub UNICEPLAC — Sistema de reserva de laboratórios",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="pt-BR">
      <head>
        <link
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet"
        />
        <link
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"
          rel="stylesheet"
        />
      </head>
      <body>{children}</body>
    </html>
  );
}
