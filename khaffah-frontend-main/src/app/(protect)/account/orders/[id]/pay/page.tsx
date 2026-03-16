"use client";

import { useParams, useRouter } from "next/navigation";
import { useState, useEffect } from "react";
import Link from "next/link";
import { ChevronLeft } from "lucide-react";
import { Order } from "@/types/order";
import type { PaymentPayload, CompanyBank } from "@/types/payment";
import PaymentStep1 from "@/components/pages/account/payment_step1";
import PaymentStep2 from "@/components/pages/account/payment_step2";
import { apiInstance } from "@/lib/axios";

function mapRekeningToCompanyBank(list: { id: number; bank_name: string; account_number: string; account_holder_name: string }[]): CompanyBank[] {
  return list.map((r) => ({
    id: String(r.id),
    name: r.bank_name,
    accountNumber: r.account_number,
    accountHolder: r.account_holder_name,
  }));
}

export default function OrderPayPage() {
  const params = useParams();
  const router = useRouter();
  const id = params?.id as string;
  const [order, setOrder] = useState<Order | null>(null);
  const [companyBanks, setCompanyBanks] = useState<CompanyBank[]>([]);
  const [loadingRekening, setLoadingRekening] = useState(true);
  const [loading, setLoading] = useState(true);
  const [notFound, setNotFound] = useState(false);
  const [step, setStep] = useState<1 | 2>(1);
  const [payload, setPayload] = useState<PaymentPayload | null>(null);

  useEffect(() => {
    if (!id) {
      setLoading(false);
      setNotFound(true);
      return;
    }
    let cancelled = false;
    setLoading(true);
    fetch(`/api/account/orders/${id}`, { credentials: "include", cache: "no-store" })
      .then((res) => res.json().catch(() => ({})))
      .then((json) => {
        if (cancelled) return;
        if (json?.status && json?.data) {
          setOrder(json.data);
          setNotFound(false);
        } else {
          setNotFound(true);
        }
      })
      .catch(() => {
        if (!cancelled) setNotFound(true);
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });
    return () => {
      cancelled = true;
    };
  }, [id]);

  useEffect(() => {
    let cancelled = false;
    setLoadingRekening(true);
    apiInstance
      .get<{ status?: boolean; data?: { id: number; bank_name: string; account_number: string; account_holder_name: string }[] }>("/api/rekening/list")
      .then((res) => {
        if (cancelled) return;
        const list = res?.data?.data ?? [];
        setCompanyBanks(mapRekeningToCompanyBank(list));
      })
      .catch(() => {
        if (!cancelled) setCompanyBanks([]);
      })
      .finally(() => {
        if (!cancelled) setLoadingRekening(false);
      });
    return () => {
      cancelled = true;
    };
  }, []);

  const handleStep1Next = (p: PaymentPayload) => {
    setPayload(p);
    setStep(2);
  };

  const handleStep2Back = () => setStep(1);

  if (loading) {
    return (
      <div className="flex flex-col items-center justify-center min-h-[60vh] gap-4">
        <p className="text-muted-foreground">Memuat halaman pembayaran...</p>
      </div>
    );
  }

  if (notFound || !order) {
    return (
      <div className="flex flex-col items-center justify-center min-h-[60vh] gap-4 px-4">
        <h2 className="text-xl font-bold">Pesanan tidak ditemukan</h2>
        <Link
          href={`/account/orders/${id}`}
          className="text-teal-600 font-medium hover:underline flex items-center gap-1"
        >
          <ChevronLeft className="w-4 h-4" /> Kembali ke detail pesanan
        </Link>
      </div>
    );
  }

  return (
    <div className="w-full max-w-4xl mx-auto min-h-screen bg-gray-100 pb-24 px-4">
      <header className="sticky top-0 z-10 bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between">
        <Link
          href={step === 1 ? `/account/orders/${id}` : "#"}
          onClick={(e) => {
            if (step === 2) {
              e.preventDefault();
              handleStep2Back();
            }
          }}
          className="flex items-center text-teal-600 font-medium hover:underline gap-1 text-14"
        >
          <ChevronLeft className="w-4 h-4" /> Kembali
        </Link>
        <h1 className="text-14 font-bold text-gray-900">Pembayaran</h1>
        <span className="w-14" />
      </header>

      <main className="p-4 space-y-4">
        {step === 1 && (
          <PaymentStep1 order={order} onNext={handleStep1Next} />
        )}
        {step === 2 && payload && (
          loadingRekening ? (
            <div className="flex flex-col items-center justify-center py-12 gap-2">
              <p className="text-muted-foreground">Memuat daftar rekening...</p>
            </div>
          ) : companyBanks.length === 0 ? (
            <div className="flex flex-col items-center justify-center py-12 gap-2">
              <p className="text-muted-foreground">Rekening tidak tersedia. Silakan hubungi admin.</p>
            </div>
          ) : (
            <PaymentStep2
              payload={payload}
              companyBanks={companyBanks}
              orderId={id}
              onSuccess={() => router.push(`/account/orders/${id}`)}
            />
          )
        )}
      </main>
    </div>
  );
}
