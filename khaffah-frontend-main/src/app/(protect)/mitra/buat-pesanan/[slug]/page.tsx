"use client";

import { Suspense, useMemo, useState, useEffect } from "react";
import { useParams } from "next/navigation";
import Link from "next/link";
import { useUmrah } from "@/query/umrah";
import { productNameToSlug } from "@/lib/slug";
import ProductDescription from "@/components/pages/product/description";
import ProductGalery from "@/components/pages/product/galery";
import ProductHeadline from "../components/headline";
import ProductDetail from "@/components/pages/product/detail";
import ProductReview from "@/components/pages/product/review";

const loadingPlaceholder = (
  <div className="h-screen flex items-center justify-center">
    <p className="text-gray-500">Memuat detail paket...</p>
  </div>
);

type ResolvedState = "loading" | "not_found" | string;

function SlugProductContent() {
  const params = useParams();
  const slug = params.slug as string;
  const [resolved, setResolved] = useState<ResolvedState>("loading");
  const { data: umrahData, isLoading } = useUmrah();
  const packages = umrahData?.data?.data ?? [];

  const matchedPackage = useMemo(
    () => packages.find((pkg) => productNameToSlug(pkg.nama_paket) === slug),
    [slug, packages]
  );
  const paketUmrahId = matchedPackage?.id?.toString();

  // Keputusan not_found vs ready HANYA di client (useEffect) agar server dan first paint selalu "loading"
  useEffect(() => {
    if (isLoading) {
      setResolved("loading");
      return;
    }
    if (slug && !matchedPackage) {
      setResolved("not_found");
      return;
    }
    if (paketUmrahId) {
      setResolved(paketUmrahId);
    } else {
      setResolved("loading");
    }
  }, [isLoading, slug, matchedPackage, paketUmrahId]);

  // Server + initial client: selalu loading (resolved awal "loading")
  if (resolved === "loading") {
    return loadingPlaceholder;
  }

  if (resolved === "not_found") {
    return (
      <div className="min-h-[50vh] flex flex-col items-center justify-center px-4 py-12">
        <div className="text-center space-y-4 max-w-md">
          <p className="text-xl font-semibold text-gray-800">Paket tidak ditemukan</p>
          <p className="text-gray-600">
            Slug &quot;{slug}&quot; tidak sesuai dengan paket umrah yang tersedia. Periksa URL atau pilih dari daftar.
          </p>
          <Link
            href="/mitra/buat-pesanan"
            className="inline-flex items-center justify-center px-5 py-2.5 rounded-xl font-medium bg-khaffah-primary text-white hover:opacity-90"
          >
            Kembali ke Daftar Paket
          </Link>
        </div>
      </div>
    );
  }

  return (
    <>
      <div className="h-14" />
      <div className="space-y-6">
        <ProductGalery paketUmrahId={resolved} />
        <ProductHeadline paketUmrahId={resolved} />
        <ProductDescription paketUmrahId={resolved} />
        <ProductDetail paketUmrahId={resolved} />
        <ProductReview paketUmrahId={resolved} />
      </div>
    </>
  );
}

const SlugProduct = () => {
  return (
    <Suspense fallback={<div className="h-screen flex items-center justify-center">Loading...</div>}>
      <SlugProductContent />
    </Suspense>
  );
};

export default SlugProduct;
