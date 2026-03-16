import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  /* config options here */
  // Izinkan akses dev dari 127.0.0.1 dan localhost (agar http://127.0.0.1:3000 bisa dipakai)
  allowedDevOrigins: ["http://127.0.0.1:3000", "http://localhost:3000"],
  // turbopack root dihapus - bisa sebabkan error di beberapa environment
  images: {
    // Daftar quality yang boleh dipakai (Next.js 16+ wajib). Default 75; komponen pakai 70.
    qualities: [70, 75, 90],
    remotePatterns: [
      {
        protocol: "https",
        hostname: "api.kaffahtrip.com",
        port: "",
        pathname: "/**",
      },
      {
        protocol: "https",
        hostname: "apikaffah.paperostudio.com",
        port: "",
        pathname: "/**",
      },
      // Sumber gambar eksternal (dipakai oleh data dummy / pilihan hotel)
      {
        protocol: "https",
        hostname: "encrypted-tbn0.gstatic.com",
        port: "",
        pathname: "/**",
      },
      {
        protocol: "https",
        hostname: "lh3.googleusercontent.com",
        port: "",
        pathname: "/**",
      },
      {
        protocol: "http",
        hostname: "localhost",
        port: "8000",
        pathname: "/**",
      },
      {
        protocol: "http",
        hostname: "127.0.0.1",
        port: "8000",
        pathname: "/**",
      },
    ],
  },
  // Ensure server can be accessed
  async rewrites() {
    return [];
  },
};

export default nextConfig;
