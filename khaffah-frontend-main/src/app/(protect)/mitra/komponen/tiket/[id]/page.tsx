"use client";

import { use, useEffect } from "react";
import { useRouter } from "next/navigation";

type PageProps = {
  params: Promise<{ id: string }>;
};

/**
 * Redirect /mitra/komponen/tiket/[id] ke /mitra/komponen/tiket?maskapai=[id]
 * Form pemesanan tiket sekarang ada di halaman utama dengan dropdown pilih maskapai.
 */
const TiketKomponenDetailRedirect = (props: PageProps) => {
  const { id } = use(props.params);
  const router = useRouter();

  useEffect(() => {
    router.replace(`/mitra/komponen/tiket?maskapai=${encodeURIComponent(id)}`);
  }, [router, id]);

  return (
    <div className="space-y-4">
      <p className="text-12 text-black/60">Mengalihkan ke form pemesanan tiket...</p>
    </div>
  );
};

export default TiketKomponenDetailRedirect;
