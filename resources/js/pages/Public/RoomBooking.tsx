import { useForm, usePage } from '@inertiajs/react';
import { format, isBefore, parseISO, startOfDay, startOfMonth } from 'date-fns';
import type { FormEvent } from 'react';
import { useEffect, useMemo, useState } from 'react';

import BookingCalendar from '@/components/BookingCalendar/BookingCalendar';
import InfoCard from '@/components/InfoCard/InfoCard';
import AppLayout from '@/components/Layout/AppLayout/AppLayout';
import type { BookingCalendarEvent } from '@/types/Booking';
import type {
  RoomBookingProps,
  RoomBookingSharedPageProps,
} from '@/types/pages/Public/RoomBooking';
import {
  buildAvailabilityMap,
  findEventsForDate,
} from '@/utils/BookingAvailability';

const statusLabelMap = {
  APPROVED: 'Disetujui',
  PENDING: 'Menunggu',
  REJECTED: 'Ditolak',
} as const;

const statusClassMap = {
  APPROVED: 'bg-rose-100 text-rose-700',
  PENDING: 'bg-amber-100 text-amber-700',
  REJECTED: 'bg-slate-100 text-slate-700',
} as const;

const RoomBooking = ({ rooms, studyPrograms }: RoomBookingProps) => {
  const [month, setMonth] = useState(startOfMonth(new Date()));
  const [selectedDate, setSelectedDate] = useState<Date | undefined>();
  const [isBookingDialogOpen, setIsBookingDialogOpen] = useState(false);
  const [isFormVisible, setIsFormVisible] = useState(false);
  const [toastMessage, setToastMessage] = useState<string | null>(null);
  const [roomBookings, setRoomBookings] = useState<BookingCalendarEvent[]>([]);
  const [isRoomBookingsLoading, setIsRoomBookingsLoading] = useState(false);
  const [roomBookingsError, setRoomBookingsError] = useState<string | null>(
    null,
  );
  const { flash } = usePage<RoomBookingSharedPageProps>().props;

  const { data, setData, post, processing, errors, reset, clearErrors } =
    useForm({
      student_name: '',
      nim: '',
      study_program: '',
      phone_number: '',
      unit: '',
      activity_name: '',
      room_id: '',
      number_of_participants: '',
      selected_date: '',
      start_time: '',
      end_time: '',
      document: null as File | null,
    });

  const selectedRoom = useMemo(
    () => rooms.find((room) => String(room.id) === data.room_id),
    [data.room_id, rooms],
  );
  const selectedRoomLabel = selectedRoom?.name ?? '';
  const isRoomSelected = Boolean(selectedRoom);
  const availabilityMap = useMemo(
    () => (isRoomSelected ? buildAvailabilityMap(roomBookings) : {}),
    [isRoomSelected, roomBookings],
  );
  const selectedDayEvents = useMemo(
    () => (selectedDate ? findEventsForDate(selectedDate, roomBookings) : []),
    [roomBookings, selectedDate],
  );

  useEffect(() => {
    if (!flash?.success) {
      return;
    }

    setToastMessage(flash.success);

    if (flash.whatsappUrl) {
      window.location.assign(flash.whatsappUrl);
    }

    const timeoutId = window.setTimeout(() => setToastMessage(null), 4500);

    return () => window.clearTimeout(timeoutId);
  }, [flash?.success, flash?.whatsappUrl]);

  useEffect(() => {
    if (Object.keys(errors).length > 0) {
      setIsFormVisible(true);
    }
  }, [errors]);

  useEffect(() => {
    if (!selectedRoom) {
      setRoomBookings([]);
      setRoomBookingsError(null);
      setIsRoomBookingsLoading(false);

      return;
    }

    const controller = new AbortController();

    setIsRoomBookingsLoading(true);
    setRoomBookingsError(null);

    window
      .fetch(`/booking/rooms/${selectedRoom.id}/bookings`, {
        signal: controller.signal,
        headers: {
          Accept: 'application/json',
        },
      })
      .then(async (response) => {
        if (!response.ok) {
          throw new Error('Gagal memuat jadwal ruangan.');
        }

        const payload = (await response.json()) as {
          data?: BookingCalendarEvent[];
        };

        setRoomBookings(payload.data ?? []);
      })
      .catch((error: unknown) => {
        if (error instanceof DOMException && error.name === 'AbortError') {
          return;
        }

        setRoomBookings([]);
        setRoomBookingsError('Jadwal ruangan belum berhasil dimuat.');
      })
      .finally(() => {
        if (!controller.signal.aborted) {
          setIsRoomBookingsLoading(false);
        }
      });

    return () => controller.abort();
  }, [selectedRoom]);

  const resetBookingFlow = () => {
    setSelectedDate(undefined);
    setIsBookingDialogOpen(false);
    setIsFormVisible(false);
    setData('selected_date', '');
    setData('start_time', '');
    setData('end_time', '');
  };

  const handleRoomOptionChange = (value: string) => {
    resetBookingFlow();
    clearErrors('room_id', 'selected_date', 'start_time', 'end_time');
    setData('room_id', value);
  };

  const handleDateChange = (date: Date | undefined) => {
    if (!isRoomSelected) {
      return;
    }

    setSelectedDate(date);
    setData('selected_date', date ? format(date, 'yyyy-MM-dd') : '');
    setIsBookingDialogOpen(Boolean(date));
    clearErrors('selected_date');
  };

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    post('/booking', {
      forceFormData: true,
      onSuccess: () => {
        reset();
        setRoomBookings([]);
        setRoomBookingsError(null);
        setSelectedDate(undefined);
        setIsBookingDialogOpen(false);
        setIsFormVisible(false);
      },
    });
  };

  const selectedDateLabel = selectedDate
    ? format(selectedDate, 'dd MMMM yyyy')
    : 'Belum memilih tanggal';
  const isPastSelectedDate = selectedDate
    ? isBefore(startOfDay(selectedDate), startOfDay(new Date()))
    : false;
  const roomSummary = selectedRoom
    ? `${selectedRoom.name} · Kapasitas ${selectedRoom.capacity} orang`
    : selectedRoomLabel || 'Belum memilih ruangan';

  return (
    <AppLayout currentPath="/booking" pageTitle="Peminjaman Ruang">
      {toastMessage ? (
        <div className="pointer-events-none fixed right-4 top-4 z-50 sm:right-6 sm:top-6">
          <div className="pointer-events-auto flex max-w-md items-start gap-3 rounded-2xl border border-emerald-200 bg-white px-4 py-4 shadow-[0_18px_40px_rgba(16,24,40,0.14)]">
            <div className="mt-0.5 flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
              <svg
                viewBox="0 0 20 20"
                className="h-4 w-4"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
              >
                <path d="M4.5 10.5 8 14l7.5-8" />
              </svg>
            </div>
            <div className="flex-1">
              <p className="text-sm font-semibold text-emerald-700">
                Permintaan berhasil dikirim
              </p>
              <p className="mt-1 text-sm text-slate-600">{toastMessage}</p>
              {flash?.whatsappUrl ? (
                <a
                  href={flash.whatsappUrl}
                  target="_blank"
                  rel="noreferrer"
                  className="mt-3 inline-flex items-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                >
                  Lanjutkan ke WhatsApp Admin
                </a>
              ) : null}
            </div>
            <button
              type="button"
              onClick={() => setToastMessage(null)}
              className="rounded-full p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
              aria-label="Tutup notifikasi"
            >
              <svg
                viewBox="0 0 20 20"
                className="h-4 w-4"
                fill="none"
                stroke="currentColor"
                strokeWidth="1.8"
              >
                <path d="m5 5 10 10M15 5 5 15" />
              </svg>
            </button>
          </div>
        </div>
      ) : null}

      {isBookingDialogOpen && selectedDate ? (
        <div className="fixed inset-0 z-40 flex items-center justify-center bg-[rgba(15,23,42,0.42)] px-4 py-6">
          <div className="w-full max-w-2xl rounded-[28px] bg-white p-6 shadow-[0_26px_80px_rgba(15,23,42,0.24)] sm:p-7">
            <div className="flex items-start justify-between gap-4">
              <div>
                <p className="text-[11px] font-semibold uppercase tracking-[0.24em] text-[var(--public-text-muted)]">
                  Daftar Booking Ruangan
                </p>
                <h2 className="mt-2 text-2xl font-bold tracking-[-0.03em] text-[var(--public-primary-hover)]">
                  {selectedRoomLabel}
                </h2>
                <p className="mt-2 text-sm text-[var(--public-text-muted)]">
                  {format(selectedDate, 'dd MMMM yyyy')}
                </p>
              </div>
              <button
                type="button"
                onClick={() => setIsBookingDialogOpen(false)}
                className="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-[var(--public-border)] text-[var(--public-primary-hover)] transition hover:bg-[var(--public-surface-soft)]"
                aria-label="Tutup popup booking"
              >
                <svg
                  viewBox="0 0 20 20"
                  className="h-4 w-4"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="1.8"
                >
                  <path d="m5 5 10 10M15 5 5 15" />
                </svg>
              </button>
            </div>

            <div className="mt-6 max-h-[46vh] space-y-3 overflow-y-auto pr-1">
              {isRoomBookingsLoading ? (
                <div className="rounded-2xl border border-dashed border-[var(--public-border)] bg-[var(--public-surface-soft)] px-4 py-5 text-sm text-[var(--public-text-muted)]">
                  Jadwal ruangan sedang dimuat...
                </div>
              ) : roomBookingsError ? (
                <div className="rounded-2xl border border-red-200 bg-red-50 px-4 py-5 text-sm text-red-700">
                  {roomBookingsError}
                </div>
              ) : selectedDayEvents.length > 0 ? (
                selectedDayEvents.map((booking) => (
                  <div
                    key={booking.id}
                    className="rounded-2xl border border-[var(--public-border)] bg-[var(--public-surface-soft)] px-4 py-4"
                  >
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                      <div>
                        <p className="text-base font-semibold text-[var(--public-primary-hover)]">
                          {booking.studentName}
                        </p>
                        <p className="mt-1 text-sm text-[var(--public-text-muted)]">
                          {booking.activityName}
                        </p>
                        <p className="mt-2 text-sm text-slate-500">
                          {format(parseISO(booking.start), 'HH:mm')} -{' '}
                          {format(parseISO(booking.end), 'HH:mm')}
                          {booking.unit ? ` · ${booking.unit}` : ''}
                        </p>
                      </div>
                      <span
                        className={`inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold ${statusClassMap[booking.status]}`}
                      >
                        {statusLabelMap[booking.status]}
                      </span>
                    </div>
                  </div>
                ))
              ) : (
                <div className="rounded-2xl border border-dashed border-[var(--public-border)] bg-[var(--public-surface-soft)] px-4 py-5 text-sm text-[var(--public-text-muted)]">
                  Belum ada booking untuk ruangan ini pada tanggal tersebut.
                  Anda bisa lanjut mengajukan peminjaman.
                </div>
              )}
            </div>

            {isPastSelectedDate ? (
              <div className="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
                Tanggal yang sudah terlewat hanya bisa dilihat riwayat
                booking-nya. Pengajuan booking baru hanya tersedia untuk hari
                ini dan tanggal setelahnya.
              </div>
            ) : null}

            <div className="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
              <button
                type="button"
                onClick={() => setIsBookingDialogOpen(false)}
                className="rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm font-semibold text-[var(--public-primary-hover)] transition hover:bg-[var(--public-surface-soft)]"
              >
                Tutup
              </button>
              <button
                type="button"
                disabled={isPastSelectedDate}
                onClick={() => {
                  if (isPastSelectedDate) {
                    return;
                  }

                  setIsFormVisible(true);
                  setIsBookingDialogOpen(false);
                }}
                className="rounded-2xl bg-[var(--public-accent)] px-5 py-3 text-sm font-semibold text-[var(--public-primary-hover)] shadow-[0_12px_24px_rgba(244,196,48,0.28)] transition hover:brightness-95 disabled:cursor-not-allowed disabled:opacity-50 disabled:shadow-none"
              >
                Pinjam Tempat
              </button>
            </div>
          </div>
        </div>
      ) : null}

      <section className="py-8 sm:py-10">
        <div className="public-container">
          <h1 className="text-4xl font-bold tracking-[-0.05em] text-[var(--public-primary-hover)] sm:text-5xl">
            Peminjaman Ruang
          </h1>
        </div>
      </section>

      <section className="pb-8">
        <div className="public-container">
          <div className="public-card p-6 sm:p-8">
            <div className="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
              <div>
                <p className="text-[11px] font-semibold uppercase tracking-[0.24em] text-[var(--public-text-muted)]">
                  Langkah 1
                </p>
                <h2 className="mt-2 text-2xl font-bold tracking-[-0.03em] text-[var(--public-primary-hover)]">
                  Pilih Ruangan Terlebih Dahulu
                </h2>
                <p className="mt-2 max-w-2xl text-sm text-[var(--public-text-muted)]">
                  Pilih ruangan dari data master yang dikelola admin. Kalender
                  dan daftar peminjam akan memuat jadwal sesuai ruangan yang
                  Anda pilih.
                </p>
              </div>
              <div className="rounded-2xl bg-[var(--public-surface-soft)] px-4 py-3 text-sm text-[var(--public-text-muted)]">
                Jadwal ruangan dimuat langsung dari server saat Anda memilih
                ruangan.
              </div>
            </div>

            <div className="mt-6 grid gap-5 lg:grid-cols-[1.3fr_0.7fr]">
              <div>
                <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                  Daftar Ruangan
                </label>
                <select
                  value={data.room_id}
                  onChange={(event) =>
                    handleRoomOptionChange(event.target.value)
                  }
                  className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                >
                  <option value="">Pilih Ruangan</option>
                  {rooms.map((room) => (
                    <option key={room.id} value={room.id}>
                      {room.name} (Kapasitas {room.capacity})
                    </option>
                  ))}
                </select>
                {errors.room_id ? (
                  <p className="mt-2 text-xs text-red-600">{errors.room_id}</p>
                ) : null}
              </div>

              <div className="rounded-2xl border border-[rgba(31,42,102,0.08)] bg-white px-5 py-4">
                <p className="text-[11px] font-semibold uppercase tracking-[0.2em] text-[var(--public-text-muted)]">
                  Ruangan Aktif
                </p>
                <p className="mt-2 text-lg font-semibold text-[var(--public-primary-hover)]">
                  {selectedRoomLabel || 'Belum dipilih'}
                </p>
                <p className="mt-1 text-sm text-[var(--public-text-muted)]">
                  {roomSummary}
                </p>
              </div>
            </div>

            {roomBookingsError ? (
              <p className="mt-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {roomBookingsError}
              </p>
            ) : null}
          </div>
        </div>
      </section>

      <section className="pb-12">
        <div className="public-container">
          <div className="grid gap-7 xl:grid-cols-[1.7fr_0.82fr]">
            <BookingCalendar
              selectedDate={selectedDate}
              month={month}
              onSelectDate={handleDateChange}
              onMonthChange={setMonth}
              availabilityMap={availabilityMap}
              isRoomSelected={isRoomSelected}
              selectedRoomLabel={selectedRoomLabel}
            />

            <div className="space-y-5">
              <InfoCard title="Informasi Penting">
                <ul className="space-y-3 text-[15px] leading-7">
                  <li>
                    Pilih ruangan terlebih dahulu, lalu klik tanggal pada
                    kalender.
                  </li>
                  <li>
                    Setelah tanggal diklik, popup akan menampilkan daftar
                    peminjam ruangan tersebut.
                  </li>
                  <li>
                    Tanggal yang sudah lewat hanya bisa dilihat sebagai riwayat,
                    tidak bisa diajukan booking baru.
                  </li>
                  <li>
                    Tekan tombol Pinjam Tempat untuk membuka form pengajuan di
                    bawah kalender untuk tanggal hari ini atau berikutnya.
                  </li>
                </ul>
              </InfoCard>

              <div className="rounded-[24px] border border-[rgba(31,42,102,0.12)] bg-[var(--public-primary-hover)] p-6 text-white shadow-[var(--public-shadow)]">
                <p className="text-white/56 text-[11px] font-semibold uppercase tracking-[0.22em]">
                  Ringkasan Pilihan
                </p>
                <p className="mt-2 text-[1.4rem] font-bold tracking-[-0.04em] text-white">
                  {selectedRoomLabel || 'Belum memilih ruangan'}
                </p>
                <p className="mt-2 text-sm text-white/80">
                  {selectedDateLabel}
                </p>
                <div className="mt-5 h-px bg-white/20" />
                <p className="mt-5 text-sm text-white/80">
                  {isRoomBookingsLoading
                    ? 'Memuat jadwal ruangan terpilih...'
                    : isRoomSelected
                      ? isPastSelectedDate
                        ? `${selectedDayEvents.length} booking ditemukan pada tanggal lampau. Mode lihat saja aktif.`
                        : `${selectedDayEvents.length} booking ditemukan pada tanggal terpilih.`
                      : 'Tentukan ruangan untuk mulai melihat ketersediaan.'}
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="py-12">
        <div className="public-container">
          <div className="public-card p-6 sm:p-8">
            {isFormVisible && !isPastSelectedDate ? (
              <>
                <div className="flex flex-col gap-4 border-b border-[var(--public-border)] pb-6 sm:flex-row sm:items-start sm:justify-between">
                  <div>
                    <h2 className="text-2xl font-bold tracking-[-0.03em] text-[var(--public-primary-hover)]">
                      Form Booking Lab / Ruang
                    </h2>
                    <p className="mt-2 text-sm text-[var(--public-text-muted)]">
                      Lengkapi data berikut untuk mengajukan peminjaman pada
                      jadwal yang sudah Anda pilih.
                    </p>
                  </div>
                  <div className="rounded-2xl bg-[var(--public-surface-soft)] px-4 py-3 text-sm text-[var(--public-text-muted)]">
                    <p>
                      <span className="font-semibold text-[var(--public-primary-hover)]">
                        Ruangan:
                      </span>{' '}
                      {selectedRoomLabel || '-'}
                    </p>
                    <p className="mt-1">
                      <span className="font-semibold text-[var(--public-primary-hover)]">
                        Tanggal:
                      </span>{' '}
                      {selectedDateLabel}
                    </p>
                  </div>
                </div>

                <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
                  <div className="grid gap-5 md:grid-cols-2">
                    <div>
                      <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                        Nama Mahasiswa
                      </label>
                      <input
                        value={data.student_name}
                        onChange={(event) =>
                          setData('student_name', event.target.value)
                        }
                        type="text"
                        className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                      />
                      {errors.student_name ? (
                        <p className="mt-2 text-xs text-red-600">
                          {errors.student_name}
                        </p>
                      ) : null}
                    </div>

                    <div>
                      <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                        NIM
                      </label>
                      <input
                        value={data.nim}
                        onChange={(event) => setData('nim', event.target.value)}
                        type="text"
                        className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                      />
                      {errors.nim ? (
                        <p className="mt-2 text-xs text-red-600">
                          {errors.nim}
                        </p>
                      ) : null}
                    </div>

                    <div>
                      <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                        Program Studi
                      </label>
                      <select
                        value={data.study_program}
                        onChange={(event) =>
                          setData('study_program', event.target.value)
                        }
                        className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                      >
                        <option value="">Pilih Program Studi</option>
                        {studyPrograms.map((program) => (
                          <option key={program} value={program}>
                            {program}
                          </option>
                        ))}
                      </select>
                      {errors.study_program ? (
                        <p className="mt-2 text-xs text-red-600">
                          {errors.study_program}
                        </p>
                      ) : null}
                    </div>

                    <div>
                      <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                        Nomor WhatsApp
                      </label>
                      <input
                        value={data.phone_number}
                        onChange={(event) =>
                          setData('phone_number', event.target.value)
                        }
                        type="tel"
                        className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                      />
                      {errors.phone_number ? (
                        <p className="mt-2 text-xs text-red-600">
                          {errors.phone_number}
                        </p>
                      ) : null}
                    </div>

                    <div>
                      <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                        Unit / Organisasi
                      </label>
                      <input
                        value={data.unit}
                        onChange={(event) =>
                          setData('unit', event.target.value)
                        }
                        type="text"
                        className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                      />
                      {errors.unit ? (
                        <p className="mt-2 text-xs text-red-600">
                          {errors.unit}
                        </p>
                      ) : null}
                    </div>

                    <div>
                      <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                        Jumlah Peserta
                      </label>
                      <input
                        value={data.number_of_participants}
                        onChange={(event) =>
                          setData('number_of_participants', event.target.value)
                        }
                        type="number"
                        min={1}
                        className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                      />
                      {errors.number_of_participants ? (
                        <p className="mt-2 text-xs text-red-600">
                          {errors.number_of_participants}
                        </p>
                      ) : null}
                    </div>

                    <div>
                      <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                        Jam Mulai
                      </label>
                      <input
                        value={data.start_time}
                        onChange={(event) =>
                          setData('start_time', event.target.value)
                        }
                        type="time"
                        className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                      />
                      {errors.start_time ? (
                        <p className="mt-2 text-xs text-red-600">
                          {errors.start_time}
                        </p>
                      ) : null}
                    </div>

                    <div>
                      <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                        Jam Selesai
                      </label>
                      <input
                        value={data.end_time}
                        onChange={(event) =>
                          setData('end_time', event.target.value)
                        }
                        type="time"
                        className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                      />
                      {errors.end_time ? (
                        <p className="mt-2 text-xs text-red-600">
                          {errors.end_time}
                        </p>
                      ) : null}
                    </div>
                  </div>

                  <div>
                    <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                      Nama Kegiatan
                    </label>
                    <textarea
                      value={data.activity_name}
                      onChange={(event) =>
                        setData('activity_name', event.target.value)
                      }
                      rows={3}
                      className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                    />
                    {errors.activity_name ? (
                      <p className="mt-2 text-xs text-red-600">
                        {errors.activity_name}
                      </p>
                    ) : null}
                  </div>

                  <div>
                    <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                      Dokumen Pendukung
                    </label>
                    <input
                      onChange={(event) =>
                        setData('document', event.target.files?.[0] ?? null)
                      }
                      type="file"
                      accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                      className="w-full rounded-2xl border border-[var(--public-border)] bg-white px-5 py-3 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-[var(--public-primary-hover)] file:px-4 file:py-2 file:text-white"
                    />
                    {errors.document ? (
                      <p className="mt-2 text-xs text-red-600">
                        {errors.document}
                      </p>
                    ) : null}
                  </div>

                  <div className="flex justify-end">
                    <button
                      type="submit"
                      disabled={processing}
                      className="rounded-2xl bg-[var(--public-accent)] px-10 py-4 text-base font-semibold text-[var(--public-primary-hover)] shadow-[0_10px_22px_rgba(244,196,48,0.28)] transition hover:brightness-95 disabled:cursor-not-allowed disabled:opacity-70"
                    >
                      {processing ? 'Mengirim...' : 'Ajukan Peminjaman'}
                    </button>
                  </div>
                </form>
              </>
            ) : isPastSelectedDate && selectedDate ? (
              <div className="rounded-[26px] border border-amber-200 bg-amber-50 px-6 py-10 text-center">
                <p className="text-[11px] font-semibold uppercase tracking-[0.24em] text-amber-700">
                  Mode Lihat Saja
                </p>
                <h2 className="mt-3 text-2xl font-bold tracking-[-0.03em] text-[var(--public-primary-hover)]">
                  Booking Baru Tidak Tersedia Untuk Tanggal Yang Sudah Lewat
                </h2>
                <p className="mx-auto mt-3 max-w-2xl text-sm leading-7 text-amber-800">
                  Anda tetap bisa melihat siapa yang sudah melakukan booking
                  pada {selectedDateLabel}, tetapi pengajuan baru hanya bisa
                  dilakukan untuk hari ini dan tanggal setelahnya.
                </p>
              </div>
            ) : (
              <div className="rounded-[26px] border border-dashed border-[var(--public-border)] bg-[var(--public-surface-soft)] px-6 py-10 text-center">
                <p className="text-[11px] font-semibold uppercase tracking-[0.24em] text-[var(--public-text-muted)]">
                  Langkah 3
                </p>
                <h2 className="mt-3 text-2xl font-bold tracking-[-0.03em] text-[var(--public-primary-hover)]">
                  Form Akan Muncul Setelah Anda Menekan Pinjam Tempat
                </h2>
                <p className="mx-auto mt-3 max-w-2xl text-sm leading-7 text-[var(--public-text-muted)]">
                  Pilih ruangan, klik tanggal di kalender, lalu lanjutkan dari
                  popup daftar booking untuk membuka form pengajuan.
                </p>
              </div>
            )}
          </div>
        </div>
      </section>
    </AppLayout>
  );
};

export default RoomBooking;
