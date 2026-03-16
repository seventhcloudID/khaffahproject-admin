"use client";

import { useParams, useRouter } from "next/navigation";
import { useState, useEffect } from "react";
import Link from "next/link";
import { ChevronLeft, CreditCard, Loader2, AlertCircle } from "lucide-react";
import type { Order } from "@/types/order";
import type { PaymentPayload, CompanyBank } from "@/types/payment";
import PaymentStep1 from "@/components/pages/account/payment_step1";
import PaymentStep2 from "@/components/pages/account/payment_step2";
import { apiInstance } from "@/lib/axios";

const detailHref = (id: string) => `/mitra/pesanan/${id}`;

function mapRekeningToCompanyBank(list: { id: number; bank_name: string; account_number: string; account_holder_name: string }[]): CompanyBank[] {
  return list.map((r) => ({
    id: String(r.id),
    name: r.bank_name,
    accountNumber: r.account_number,
    accountHolder: r.account_holder_name,
  }));
}

export default function MitraOrderPayPage() {
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
    fetch(`/api/account/orders/${id}`, {
      credentials: "include",
      cache: "no-store",
    })
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
      <div className="h-screen flex flex-col items-center justify-center gap-6 px-4 bg-white">
        <Loader2 className="w-10 h-10 animate-spin text-khaffah-primary" />
        <p className="text-khaffah-neutral-dark text-sm font-medium">Memuat halaman pembayaran...</p>
      </div>
    );
  }

  if (notFound || !order) {
    return (
      <div className="h-screen flex flex-col items-center justify-center gap-6 px-4 bg-white">
        <div className="rounded-2xl border border-gray-200 p-8 text-center max-w-md bg-white">
          <div className="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4">
            <AlertCircle className="w-7 h-7 text-red-500" />
          </div>
          <h2 className="text-xl font-bold text-gray-900 mb-2">Pesanan tidak ditemukan</h2>
          <p className="text-khaffah-neutral-dark text-sm mb-6">ID pesanan tidak valid atau Anda tidak memiliki akses.</p>
          <Link
            href={detailHref(id)}
            className="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-medium bg-khaffah-primary text-white hover:opacity-90 transition-opacity"
          >
            <ChevronLeft className="w-4 h-4" /> Kembali ke Daftar Pesanan
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="h-screen flex flex-col bg-white overflow-hidden">
      <header className="flex-none border-b border-gray-200">
        <div className="max-w-2xl mx-auto px-4 py-3">
          <div className="flex items-center justify-between gap-4">
            <Link
              href={step === 1 ? detailHref(id) : "#"}
              onClick={(e) => {
                if (step === 2) {
                  e.preventDefault();
                  handleStep2Back();
                }
              }}
              className="flex items-center gap-1.5 text-khaffah-primary font-semibold text-sm hover:opacity-80 transition-opacity"
            >
              <ChevronLeft className="w-5 h-5" /> Kembali
            </Link>
            <h1 className="text-base font-bold text-gray-900">Pembayaran</h1>
            <span className="w-16" aria-hidden />
          </div>
          <div className="mt-3 flex items-center gap-2">
            <div className="flex flex-1 items-center gap-1">
              <span className={`flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold ${step >= 1 ? "bg-khaffah-primary text-white" : "bg-gray-200 text-gray-500"}`}>
                1
              </span>
              <span className={`text-xs font-medium ${step >= 1 ? "text-gray-900" : "text-gray-400"}`}>Nominal</span>
            </div>
            <div className="h-0.5 flex-1 max-w-[60px] rounded-full bg-gray-200" />
            <div className="flex flex-1 items-center gap-1">
              <span className={`flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold ${step >= 2 ? "bg-khaffah-primary text-white" : "bg-gray-200 text-gray-500"}`}>
                2
              </span>
              <span className={`text-xs font-medium ${step >= 2 ? "text-gray-900" : "text-gray-400"}`}>Transfer</span>
            </div>
          </div>
        </div>
      </header>

      <main className="flex-1 min-h-0 overflow-hidden flex flex-col">
        <div className="max-w-2xl mx-auto w-full px-4 py-4 space-y-4 flex-1 min-h-0 overflow-hidden">
          {step === 1 && (
            <PaymentStep1 order={order} onNext={handleStep1Next} />
          )}
          {step === 2 && payload && (
            loadingRekening ? (
              <div className="rounded-2xl border border-gray-200 p-12 flex flex-col items-center justify-center gap-4 bg-white">
                <Loader2 className="w-10 h-10 animate-spin text-khaffah-primary" />
                <p className="text-khaffah-neutral-dark text-sm font-medium">Memuat daftar rekening...</p>
              </div>
            ) : companyBanks.length === 0 ? (
              <div className="rounded-2xl border border-gray-200 p-8 flex flex-col items-center justify-center gap-4 text-center bg-white">
                <div className="w-12 h-12 rounded-full bg-amber-50 flex items-center justify-center">
                  <CreditCard className="w-6 h-6 text-amber-600" />
                </div>
                <p className="text-gray-900 font-semibold">Rekening tidak tersedia</p>
                <p className="text-khaffah-neutral-dark text-sm max-w-xs">Silakan hubungi admin untuk mengaktifkan rekening pembayaran.</p>
              </div>
            ) : (
              <PaymentStep2
                payload={payload}
                companyBanks={companyBanks}
                orderId={id}
                onSuccess={() => router.push(detailHref(id))}
              />
            )
          )}
        </div>
      </main>
    </div>
  );
}
