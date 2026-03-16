"use client";

import Link from "next/link";
import { useCallback, useEffect, useMemo, useRef, useState } from "react";
import { ChevronRight, Upload, Calendar, ChevronDown, User, Lock } from "lucide-react";
import { SuccessModal } from "@/components/pages/mitra/ui/SuccessModal";

import type { MitraProfile, Gender } from "@/types/mitra";
import { AvatarCircle } from "@/components/pages/mitra/ui/AvatarCircle";

/** Response GET /api/me (backend) */
interface MeResponse {
  id: number;
  nama_lengkap: string;
  jenis_kelamin: string;
  tgl_lahir: string | null;
  email: string;
  no_handphone: string;
  foto_profile: string | null;
  roles?: unknown;
  subroles?: unknown;
}

function emptyProfile(): MitraProfile {
  return {
    fullName: "",
    gender: "L",
    birthDate: "",
    email: "",
    phone: "",
    avatarUrl: null,
  };
}

function meToProfile(me: MeResponse): MitraProfile {
  const gender: Gender =
    me.jenis_kelamin === "perempuan" ? "P" : "L";
  const tgl = me.tgl_lahir ?? "";
  const birthDate = tgl.includes("T") ? tgl.slice(0, 10) : tgl;
  return {
    fullName: me.nama_lengkap ?? "",
    gender,
    birthDate,
    email: me.email ?? "",
    phone: me.no_handphone ?? "",
    avatarUrl: me.foto_profile ?? null,
  };
}

function profileToPayload(form: MitraProfile) {
  return {
    nama_lengkap: form.fullName,
    jenis_kelamin: form.gender === "P" ? "perempuan" : "laki-laki",
    tgl_lahir: form.birthDate || null,
    email: form.email,
    no_handphone: form.phone,
  };
}

/* ======================= Page ======================= */

export default function EditProfilePage() {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [baseline, setBaseline] = useState<MitraProfile>(emptyProfile);
  const [form, setForm] = useState<MitraProfile>(emptyProfile);

  const [avatar, setAvatar] = useState<string | null>(null);
  const [avatarFile, setAvatarFile] = useState<File | null>(null);
  const avatarRef = useRef<HTMLInputElement | null>(null);
  const pickAvatar = () => avatarRef.current?.click();
  const onAvatar = (e: React.ChangeEvent<HTMLInputElement>) => {
    const f = e.target.files?.[0];
    if (!f) return;
    setAvatar(URL.createObjectURL(f));
    setAvatarFile(f);
  };

  const fetchProfile = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const res = await fetch("/api/auth/me", { cache: "no-store" });
      if (!res.ok) {
        setError("Gagal memuat profil.");
        return;
      }
      const me: MeResponse = await res.json();
      const profile = meToProfile(me);
      setBaseline(profile);
      setForm(profile);
      setAvatar(profile.avatarUrl ?? null);
    } catch {
      setError("Gagal memuat profil.");
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchProfile();
  }, [fetchProfile]);

  const [dirty, setDirty] = useState(false);
  useEffect(() => {
    if (loading) return;
    const changed =
      form.fullName !== baseline.fullName ||
      form.gender !== baseline.gender ||
      form.birthDate !== baseline.birthDate ||
      form.email !== baseline.email ||
      form.phone !== baseline.phone ||
      avatarFile !== null ||
      (avatar ?? "") !== (baseline.avatarUrl ?? "");
    setDirty(changed);
  }, [form, baseline, avatar, avatarFile, loading]);

  const [successOpen, setSuccessOpen] = useState(false);
  const [saving, setSaving] = useState(false);
  const [saveError, setSaveError] = useState<string | null>(null);
  const [openBirth, setOpenBirth] = useState(false);

  const prettyBirth = useMemo(
    () =>
      form.birthDate
        ? parseLocalISO(form.birthDate).toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "long",
            year: "numeric",
          })
        : "-",
    [form.birthDate]
  );

  function setField<K extends keyof MitraProfile>(
    key: K,
    value: MitraProfile[K]
  ) {
    setForm((s) => ({ ...s, [key]: value }));
  }

  async function handleSave() {
    if (!dirty || saving) return;
    setSaving(true);
    setSaveError(null);
    try {
      const payload = profileToPayload(form);
      const resProfile = await fetch("/api/account/profile", {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      const dataProfile = await resProfile.json().catch(() => ({}));
      if (!resProfile.ok) {
        const msg =
          dataProfile?.errors
            ? Object.values(dataProfile.errors).flat().join(" ")
            : dataProfile?.message ?? "Gagal memperbarui profil.";
        setSaveError(msg);
        return;
      }

      let newAvatarUrl: string | null = baseline.avatarUrl ?? null;
      if (avatarFile) {
        const fd = new FormData();
        fd.append("foto_profile", avatarFile);
        const resPhoto = await fetch("/api/account/profile/photo", {
          method: "POST",
          body: fd,
        });
        const dataPhoto = await resPhoto.json().catch(() => ({}));
        if (resPhoto.ok && dataPhoto.foto_profile) {
          newAvatarUrl = dataPhoto.foto_profile;
        }
      }

      const updated: MitraProfile = { ...form, avatarUrl: newAvatarUrl };
      setBaseline(updated);
      setForm(updated);
      setAvatar(newAvatarUrl);
      setAvatarFile(null);
      setSuccessOpen(true);
      setTimeout(() => setSuccessOpen(false), 1600);
    } catch {
      setSaveError("Gagal memperbarui profil.");
    } finally {
      setSaving(false);
    }
  }

  if (loading) {
    return (
      <div className="space-y-6">
        <div className="flex items-center gap-3">
          <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-khaffah-primary/10">
            <User className="h-5 w-5 text-khaffah-primary" />
          </div>
          <div>
            <h1 className="text-xl font-semibold text-foreground">Profile Kamu</h1>
            <p className="text-sm text-muted-foreground">Memuat profil…</p>
          </div>
        </div>
        <div className="h-48 animate-pulse rounded-2xl border border-border bg-card/50" />
      </div>
    );
  }

  if (error) {
    return (
      <div className="space-y-6">
        <div className="flex items-center gap-3">
          <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-khaffah-primary/10">
            <User className="h-5 w-5 text-khaffah-primary" />
          </div>
          <div>
            <h1 className="text-xl font-semibold text-foreground">Profile Kamu</h1>
            <p className="text-sm text-muted-foreground">Terjadi kesalahan</p>
          </div>
        </div>
        <div className="rounded-2xl border border-destructive/30 bg-destructive/5 p-6">
          <p className="text-sm text-destructive">{error}</p>
          <button
            type="button"
            onClick={() => fetchProfile()}
            className="mt-4 rounded-lg border border-border bg-card px-4 py-2 text-sm font-medium hover:bg-foreground/5"
          >
            Coba lagi
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center gap-3">
        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-khaffah-primary/10">
          <User className="h-5 w-5 text-khaffah-primary" />
        </div>
        <div>
          <h1 className="text-xl font-semibold text-foreground">Profile Kamu</h1>
          <p className="text-sm text-muted-foreground">
            Kelola informasi pribadi dan keamanan akun Anda.
          </p>
        </div>
      </div>

      <div className="grid grid-cols-12 gap-6 lg:gap-8">
        {/* Sidebar: Foto profil */}
        <aside className="col-span-12 lg:col-span-4">
          <div className="rounded-2xl border border-border bg-card p-6 shadow-sm">
            <div className="flex flex-col items-center text-center">
              <AvatarCircle name={form.fullName} src={avatar} size={112} />
              <input
                ref={avatarRef}
                type="file"
                accept="image/*"
                className="hidden"
                onChange={onAvatar}
              />
              <p className="mt-3 text-sm font-medium text-foreground">
                {form.fullName || "Nama belum diisi"}
              </p>
              <p className="text-xs text-muted-foreground">{form.email || "—"}</p>
              <div className="mt-4 flex flex-wrap justify-center gap-2">
                <button
                  onClick={pickAvatar}
                  type="button"
                  className="inline-flex items-center gap-2 rounded-lg bg-khaffah-primary/10 px-4 py-2 text-sm font-medium text-khaffah-primary hover:bg-khaffah-primary/20"
                >
                  <Upload className="h-4 w-4" />
                  Ganti Foto
                </button>
                {(avatar || avatarFile) && (
                  <button
                    type="button"
                    onClick={() => {
                      setAvatar(baseline.avatarUrl ?? null);
                      setAvatarFile(null);
                    }}
                    className="rounded-lg border border-border px-4 py-2 text-sm hover:bg-foreground/5"
                  >
                    Batalkan
                  </button>
                )}
              </div>
              <p className="mt-4 text-[11px] text-muted-foreground">
                Maks. 1 MB · JPEG atau PNG
              </p>
            </div>
          </div>
        </aside>

        {/* Konten utama: Form & tautan */}
        <section className="col-span-12 lg:col-span-8 space-y-6">
          {/* Informasi pribadi */}
          <div className="rounded-2xl border border-border bg-card p-6 shadow-sm">
            <h2 className="mb-4 text-base font-semibold text-foreground">
              Informasi Pribadi
            </h2>
            <div className="space-y-4">
              <Row label="Nama Lengkap">
                <InputPill
                  value={form.fullName}
                  onChange={(v) => setField("fullName", v)}
                  placeholder="Masukkan nama lengkap"
                />
              </Row>
              <Row label="Jenis Kelamin">
                <div className="flex items-center gap-6 rounded-full border border-transparent bg-muted/50 px-4 py-2.5">
                  <label className="inline-flex cursor-pointer items-center gap-2 text-sm">
                    <input
                      type="radio"
                      name="gender"
                      className="h-4 w-4 accent-khaffah-primary"
                      checked={form.gender === "L"}
                      onChange={() => setField("gender", "L" as Gender)}
                    />
                    Laki-Laki
                  </label>
                  <label className="inline-flex cursor-pointer items-center gap-2 text-sm">
                    <input
                      type="radio"
                      name="gender"
                      className="h-4 w-4 accent-khaffah-primary"
                      checked={form.gender === "P"}
                      onChange={() => setField("gender", "P" as Gender)}
                    />
                    Perempuan
                  </label>
                </div>
              </Row>
              <Row label="Tanggal Lahir">
                <DatePicker
                  value={form.birthDate}
                  onChange={(iso) => setField("birthDate", iso)}
                  open={openBirth}
                  onOpen={() => setOpenBirth(true)}
                  onClose={() => setOpenBirth(false)}
                  title="Tanggal Lahir"
                >
                  <button
                    type="button"
                    className="relative flex w-full items-center justify-between rounded-full border border-transparent bg-muted/50 px-4 py-2.5 text-sm"
                  >
                    <Calendar className="absolute left-3 h-4 w-4 text-muted-foreground" />
                    <span className="font-medium">
                      {form.birthDate ? prettyBirth : "Pilih tanggal lahir"}
                    </span>
                    <ChevronDown className="h-4 w-4 text-muted-foreground" />
                  </button>
                </DatePicker>
              </Row>
              <Row label="Email">
                <InputPill
                  type="email"
                  value={form.email}
                  onChange={(v) => setField("email", v)}
                  placeholder="email@contoh.com"
                />
              </Row>
              <Row label="No. Handphone">
                <InputPill
                  type="tel"
                  value={form.phone}
                  onChange={(v) => setField("phone", v)}
                  placeholder="08xxxxxxxxxx"
                />
              </Row>
            </div>
            <div className="mt-6 flex flex-wrap items-center gap-3 border-t border-border pt-6">
              {saveError && (
                <p className="w-full text-sm text-destructive">{saveError}</p>
              )}
              <button
                onClick={handleSave}
                disabled={!dirty || saving}
                className={[
                  "rounded-lg px-5 py-2.5 text-sm font-medium text-white transition",
                  dirty && !saving
                    ? "bg-khaffah-primary hover:brightness-95"
                    : "bg-khaffah-primary/40 cursor-not-allowed",
                ].join(" ")}
              >
                {saving ? "Menyimpan…" : "Simpan Perubahan"}
              </button>
              {dirty && !saving && (
                <span className="text-xs text-muted-foreground">
                  Ada perubahan yang belum disimpan
                </span>
              )}
            </div>
          </div>

          {/* Tautan berguna */}
          <div className="space-y-3">
            <h2 className="text-base font-semibold text-foreground">
              Keamanan & Dokumen
            </h2>
            <div className="flex flex-col gap-2">
              <LinkRow
                href="/mitra/edit-profile/dokumen"
                icon={<Upload className="h-4 w-4 text-muted-foreground" />}
              >
                Edit Dokumen Kemitraan
              </LinkRow>
              <LinkRow
                href="/mitra/edit-profile/password"
                icon={<Lock className="h-4 w-4 text-muted-foreground" />}
              >
                Ganti Password
              </LinkRow>
            </div>
          </div>
        </section>
      </div>

      <SuccessModal
        open={successOpen}
        onClose={() => setSuccessOpen(false)}
        title="Profil Berhasil Diperbarui"
        description="Perubahan profilmu sudah disimpan."
      />
    </div>
  );
}

/* ======================= Sub Components ======================= */

function Row({
  label,
  children,
}: {
  label: string;
  children: React.ReactNode;
}) {
  return (
    <div className="grid grid-cols-12 items-center gap-3">
      <div className="col-span-12 md:col-span-3">
        <label className="text-sm font-medium text-foreground">{label}</label>
      </div>
      <div className="col-span-12 md:col-span-9">{children}</div>
    </div>
  );
}

function InputPill({
  value,
  onChange,
  type = "text",
  placeholder,
}: {
  value: string;
  onChange: (v: string) => void;
  type?: "text" | "email" | "tel";
  placeholder?: string;
}) {
  return (
    <input
      type={type}
      value={value}
      placeholder={placeholder}
      onChange={(e) => onChange(e.target.value)}
      className="w-full rounded-lg border border-border bg-muted/50 px-4 py-2.5 text-sm text-foreground outline-none transition placeholder:text-muted-foreground focus:border-khaffah-primary focus:ring-2 focus:ring-khaffah-primary/20"
    />
  );
}

function LinkRow({
  href,
  children,
  icon,
}: {
  href: string;
  children: React.ReactNode;
  icon?: React.ReactNode;
}) {
  return (
    <Link
      href={href}
      className="group flex items-center justify-between rounded-xl border border-border bg-card px-4 py-3 transition-colors hover:bg-muted/30"
    >
      <div className="flex items-center gap-3 text-sm font-medium">
        {icon ?? null}
        <span>{children}</span>
      </div>
      <ChevronRight className="h-4 w-4 text-muted-foreground transition-transform group-hover:translate-x-0.5" />
    </Link>
  );
}

/* ======================= Date Picker (Modal) ======================= */

function DatePicker({
  value,
  onChange,
  children,
  open,
  onOpen,
  onClose,
  title,
}: {
  value: string;
  onChange: (iso: string) => void;
  children: React.ReactNode;
  open: boolean;
  onOpen: () => void;
  onClose: () => void;
  title?: string;
}) {
  const [view, setView] = useState(() => {
    const d = value ? parseLocalISO(value) : new Date();
    return new Date(d.getFullYear(), d.getMonth(), 1);
  });
  const [tempISO, setTempISO] = useState<string>(value || "");
  const [squelchUntil, setSquelchUntil] = useState<number>(0);

  const todayISO = localISO(new Date());

  function safeOpen() {
    if (Date.now() < squelchUntil) return;
    setTempISO(value || "");
    const d = value ? parseLocalISO(value) : new Date();
    setView(new Date(d.getFullYear(), d.getMonth(), 1));
    onOpen();
  }
  function closeModal() {
    onClose();
    setSquelchUntil(Date.now() + 250);
    (document.activeElement as HTMLElement | null)?.blur?.();
  }

  useEffect(() => {
    if (!open) return;
    const prev = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    return () => {
      document.body.style.overflow = prev;
    };
  }, [open]);

  useEffect(() => {
    if (!open) return;
    function onKey(e: KeyboardEvent) {
      if (e.key === "Escape") closeModal();
      if (e.key === "ArrowLeft")
        setView((v) => new Date(v.getFullYear(), v.getMonth() - 1, 1));
      if (e.key === "ArrowRight")
        setView((v) => new Date(v.getFullYear(), v.getMonth() + 1, 1));
    }
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open]);

  const monthLabel = view.toLocaleDateString("id-ID", {
    month: "long",
    year: "numeric",
  });
  const grid = buildCalendarMatrixMonday(view);

  function select(day: number, type: "prev" | "curr" | "next") {
    if (type !== "curr") return;
    const d = new Date(view.getFullYear(), view.getMonth(), day);
    setTempISO(localISO(d));
  }

  function confirm() {
    if (tempISO) onChange(tempISO);
    closeModal();
  }

  return (
    <>
      <div onMouseDown={safeOpen} onClick={(e) => e.preventDefault()}>
        {children}
      </div>

      {open && (
        <>
          <div
            className="fixed inset-0 z-[59] bg-foreground/40"
            onMouseDown={closeModal}
          />
          <div className="fixed inset-0 z-[60] grid place-items-center p-4">
            <div
              role="dialog"
              aria-modal="true"
              className="w-[340px] rounded-2xl border border-border bg-card p-3 shadow-2xl"
              onMouseDown={(e) => e.stopPropagation()}
            >
              <div className="mb-2 px-1 text-sm font-medium">
                {title ?? "Pilih Tanggal"}
              </div>
              <div className="flex items-center justify-between px-1">
                <button
                  type="button"
                  onMouseDown={(e) => {
                    e.stopPropagation();
                    setView(
                      (v) => new Date(v.getFullYear(), v.getMonth() - 1, 1)
                    );
                  }}
                  className="inline-grid h-8 w-8 place-content-center rounded-full hover:bg-foreground/5"
                  aria-label="Bulan sebelumnya"
                >
                  ‹
                </button>
                <div className="text-sm font-medium">{monthLabel}</div>
                <button
                  type="button"
                  onMouseDown={(e) => {
                    e.stopPropagation();
                    setView(
                      (v) => new Date(v.getFullYear(), v.getMonth() + 1, 1)
                    );
                  }}
                  className="inline-grid h-8 w-8 place-content-center rounded-full hover:bg-foreground/5"
                  aria-label="Bulan berikutnya"
                >
                  ›
                </button>
              </div>

              <div className="mt-2 grid grid-cols-7 gap-1 px-1 text-center text-[11px] text-khaffah-neutral-dark">
                {["Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min"].map((d) => (
                  <div key={d}>{d}</div>
                ))}
              </div>

              <div className="mt-1 grid grid-cols-7 gap-1 px-1">
                {grid.map((c, i) => {
                  const { day, type } = c;
                  const base = new Date(view);
                  const date = new Date(
                    base.getFullYear(),
                    base.getMonth() +
                      (type === "prev" ? -1 : type === "next" ? 1 : 0),
                    day
                  );
                  const iso = localISO(date);
                  const selected = tempISO === iso;
                  const isToday = todayISO === iso;
                  const disabled = type !== "curr";
                  return (
                    <button
                      key={i}
                      type="button"
                      disabled={disabled}
                      onMouseDown={(e) => {
                        e.stopPropagation();
                        select(day, type);
                      }}
                      className={[
                        "h-10 rounded-full text-sm outline-none transition",
                        disabled
                          ? "text-khaffah-neutral-mid"
                          : "hover:bg-foreground/5 focus:ring-2 focus:ring-khaffah-primary",
                        selected
                          ? "bg-khaffah-primary text-white hover:brightness-95"
                          : "",
                        isToday && !selected
                          ? "ring-2 ring-khaffah-primary/30"
                          : "",
                      ].join(" ")}
                    >
                      {day}
                    </button>
                  );
                })}
              </div>

              <div className="mt-3 flex items-center justify-between gap-2 border-t border-border pt-3">
                <div className="text-xs text-khaffah-neutral-dark">
                  {tempISO ? formatDateID(tempISO) : "Belum memilih tanggal"}
                </div>
                <div className="flex gap-2">
                  <button
                    type="button"
                    onMouseDown={(e) => {
                      e.stopPropagation();
                      closeModal();
                    }}
                    className="rounded-lg border border-border px-3 py-1.5 text-sm hover:bg-foreground/5"
                  >
                    Batal
                  </button>
                  <button
                    type="button"
                    onMouseDown={(e) => {
                      e.stopPropagation();
                      confirm();
                    }}
                    disabled={!tempISO}
                    className={`rounded-lg px-3 py-1.5 text-sm font-medium text-white ${
                      tempISO
                        ? "bg-khaffah-primary hover:brightness-95"
                        : "bg-khaffah-primary/40 cursor-not-allowed"
                    }`}
                  >
                    Konfirmasi
                  </button>
                </div>
              </div>
            </div>
          </div>
        </>
      )}
    </>
  );
}

/* ======================= Utils ======================= */

function formatDateID(iso: string) {
  if (!iso) return "";
  const d = parseLocalISO(iso);
  return d.toLocaleDateString("id-ID", {
    day: "2-digit",
    month: "long",
    year: "numeric",
  });
}

function buildCalendarMatrixMonday(anchor: Date) {
  const y = anchor.getFullYear();
  const m = anchor.getMonth();
  const first = new Date(y, m, 1);
  const startIdx = (first.getDay() + 6) % 7; // Sen=0
  const daysCurr = new Date(y, m + 1, 0).getDate();
  const daysPrev = new Date(y, m, 0).getDate();

  const cells: { day: number; type: "prev" | "curr" | "next" }[] = [];
  for (let i = startIdx; i > 0; i--)
    cells.push({ day: daysPrev - i + 1, type: "prev" });
  for (let d = 1; d <= daysCurr; d++) cells.push({ day: d, type: "curr" });
  while (cells.length % 7 !== 0)
    cells.push({ day: (cells.length % 7) + 1, type: "next" });
  return cells;
}

function pad(n: number) {
  return String(n).padStart(2, "0");
}
function localISO(date: Date) {
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(
    date.getDate()
  )}`;
}
function parseLocalISO(iso: string) {
  const [y, m, d] = iso.split("-").map((x) => parseInt(x, 10));
  return new Date(y, (m || 1) - 1, d || 1);
}
