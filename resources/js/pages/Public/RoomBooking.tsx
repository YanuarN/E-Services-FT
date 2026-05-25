import { useForm, usePage } from '@inertiajs/react';
import { format, isBefore, parseISO, startOfDay, startOfMonth } from 'date-fns';
import type { FormEvent } from 'react';
import { useEffect, useMemo, useState } from 'react';

import BookingCalendar from '@/components/BookingCalendar/BookingCalendar';
import AppLayout from '@/components/Layout/AppLayout/AppLayout';
import type { BookingCalendarEvent } from '@/types/Booking';
import type {
  RoomBookingProps,
  RoomBookingSharedPageProps,
} from '@/types/pages/Public/RoomBooking';

type BookingSlotForm = {
  room_id: string;
  start_time: string;
  end_time: string;
};

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

const RequiredMark = () => (
  <span className="ml-1 text-red-600" aria-hidden="true">
    *
  </span>
);

const toMinutes = (time: string): number => {
  const [hours, minutes] = time.split(':').map(Number);

  if (!Number.isFinite(hours) || !Number.isFinite(minutes)) {
    return -1;
  }

  return hours * 60 + minutes;
};

const hasOverlap = (
  startA: string,
  endA: string,
  startB: string,
  endB: string,
): boolean => {
  const aStart = toMinutes(startA);
  const aEnd = toMinutes(endA);
  const bStart = toMinutes(startB);
  const bEnd = toMinutes(endB);

  if (aStart < 0 || aEnd < 0 || bStart < 0 || bEnd < 0) {
    return false;
  }

  return aStart < bEnd && aEnd > bStart;
};

const RoomBooking = ({ rooms, studyPrograms }: RoomBookingProps) => {
  const [month, setMonth] = useState(startOfMonth(new Date()));
  const [selectedDate, setSelectedDate] = useState<Date | undefined>();
  const [toastMessage, setToastMessage] = useState<string | null>(null);
  const [dayBookings, setDayBookings] = useState<BookingCalendarEvent[]>([]);
  const [isDayBookingsLoading, setIsDayBookingsLoading] = useState(false);
  const [dayBookingsError, setDayBookingsError] = useState<string | null>(null);
  const { flash } = usePage<RoomBookingSharedPageProps>().props;

  const { data, setData, post, processing, errors, reset } = useForm({
    student_name: '',
    nim: '',
    study_program: '',
    phone_number: '',
    unit: '',
    activity_name: '',
    number_of_participants: '',
    selected_date: '',
    booking_slots: [{ room_id: '', start_time: '', end_time: '' }] as BookingSlotForm[],
    document: null as File | null,
  });
  const formErrors = errors as Record<string, string | undefined>;

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
    if (!selectedDate) {
      setDayBookings([]);
      setDayBookingsError(null);
      setIsDayBookingsLoading(false);
      setData('selected_date', '');

      return;
    }

    const selectedDateValue = format(selectedDate, 'yyyy-MM-dd');
    setData('selected_date', selectedDateValue);

    const controller = new AbortController();

    setIsDayBookingsLoading(true);
    setDayBookingsError(null);

    window
      .fetch(`/booking/bookings?selected_date=${selectedDateValue}`, {
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

        setDayBookings(payload.data ?? []);
      })
      .catch((error: unknown) => {
        if (error instanceof DOMException && error.name === 'AbortError') {
          return;
        }

        setDayBookings([]);
        setDayBookingsError('Jadwal ruangan belum berhasil dimuat.');
      })
      .finally(() => {
        if (!controller.signal.aborted) {
          setIsDayBookingsLoading(false);
        }
      });

    return () => controller.abort();
  }, [selectedDate, setData]);

  const selectedDateLabel = selectedDate
    ? format(selectedDate, 'dd MMMM yyyy')
    : 'Belum memilih tanggal';
  const isPastSelectedDate = selectedDate
    ? isBefore(startOfDay(selectedDate), startOfDay(new Date()))
    : false;

  const handleDateChange = (date: Date | undefined) => {
    setSelectedDate(date);
  };

  const addSlotRow = () => {
    setData('booking_slots', [
      ...data.booking_slots,
      { room_id: '', start_time: '', end_time: '' },
    ]);
  };

  const removeSlotRow = (index: number) => {
    if (data.booking_slots.length <= 1) {
      return;
    }

    setData(
      'booking_slots',
      data.booking_slots.filter((_, slotIndex) => slotIndex !== index),
    );
  };

  const updateSlotRow = (
    index: number,
    field: keyof BookingSlotForm,
    value: string,
  ) => {
    const nextSlots = data.booking_slots.map((slot, slotIndex) => {
      if (slotIndex !== index) {
        return slot;
      }

      return {
        ...slot,
        [field]: value,
      };
    });

    setData('booking_slots', nextSlots);
  };

  const localConflicts = useMemo(() => {
    return data.booking_slots
      .map((slot, index) => {
        if (!slot.room_id || !slot.start_time || !slot.end_time) {
          return null;
        }

        const matched = dayBookings.find((booking) => {
          if (String(booking.roomId) !== slot.room_id) {
            return false;
          }

          const bookingStart = format(parseISO(booking.start), 'HH:mm');
          const bookingEnd = format(parseISO(booking.end), 'HH:mm');

          return hasOverlap(
            slot.start_time,
            slot.end_time,
            bookingStart,
            bookingEnd,
          );
        });

        if (!matched) {
          return null;
        }

        const roomLabel = rooms.find((room) => String(room.id) === slot.room_id)?.name;

        return {
          index,
          roomLabel: roomLabel ?? 'Ruang',
          startTime: slot.start_time,
          endTime: slot.end_time,
        };
      })
      .filter(Boolean) as {
      index: number;
      roomLabel: string;
      startTime: string;
      endTime: string;
    }[];
  }, [data.booking_slots, dayBookings, rooms]);

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    if (!selectedDate) {
      return;
    }

    if (localConflicts.length > 0) {
      return;
    }

    post('/booking', {
      forceFormData: true,
      onSuccess: () => {
        reset();
        setData('booking_slots', [{ room_id: '', start_time: '', end_time: '' }]);
      },
    });
  };

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

      <section className="py-8 sm:py-10">
        <div className="public-container">
          <h1 className="text-4xl font-bold tracking-[-0.05em] text-[var(--public-primary-hover)] sm:text-5xl">
            Peminjaman Ruang Multi-Ruangan
          </h1>
        </div>
      </section>

      <section className="pb-12">
        <div className="public-container">
          <div className="grid gap-7 xl:grid-cols-[1.3fr_1fr]">
            <BookingCalendar
              selectedDate={selectedDate}
              month={month}
              onSelectDate={handleDateChange}
              onMonthChange={setMonth}
            />

            <div className="public-card p-6">
              <p className="text-[11px] font-semibold uppercase tracking-[0.2em] text-[var(--public-text-muted)]">
                Booking Hari Terpilih
              </p>
              <p className="mt-2 text-lg font-semibold text-[var(--public-primary-hover)]">
                {selectedDateLabel}
              </p>

              {dayBookingsError ? (
                <p className="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                  {dayBookingsError}
                </p>
              ) : null}

              <div className="mt-5 space-y-3">
                {!selectedDate ? (
                  <div className="rounded-2xl border border-dashed border-[var(--public-border)] bg-[var(--public-surface-soft)] px-4 py-5 text-sm text-[var(--public-text-muted)]">
                    Klik tanggal pada kalender untuk melihat daftar booking.
                  </div>
                ) : isDayBookingsLoading ? (
                  <div className="rounded-2xl border border-dashed border-[var(--public-border)] bg-[var(--public-surface-soft)] px-4 py-5 text-sm text-[var(--public-text-muted)]">
                    Jadwal ruangan sedang dimuat...
                  </div>
                ) : dayBookings.length > 0 ? (
                  dayBookings.map((booking) => (
                    <div
                      key={booking.id}
                      className="rounded-2xl border border-[var(--public-border)] bg-[var(--public-surface-soft)] px-4 py-4"
                    >
                      <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                          <p className="text-base font-semibold text-[var(--public-primary-hover)]">
                            {booking.roomName}
                          </p>
                          <p className="mt-1 text-sm text-[var(--public-text-muted)]">
                            {booking.studentName} · {booking.activityName}
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
                    Belum ada booking pada tanggal ini.
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="py-12">
        <div className="public-container">
          <div className="public-card p-6 sm:p-8">
            {!selectedDate ? (
              <div className="rounded-[26px] border border-dashed border-[var(--public-border)] bg-[var(--public-surface-soft)] px-6 py-10 text-center">
                <h2 className="text-2xl font-bold tracking-[-0.03em] text-[var(--public-primary-hover)]">
                  Pilih Tanggal Terlebih Dahulu
                </h2>
                <p className="mx-auto mt-3 max-w-2xl text-sm leading-7 text-[var(--public-text-muted)]">
                  Klik tanggal di kalender untuk membuka form booking multi-ruangan.
                </p>
              </div>
            ) : isPastSelectedDate ? (
              <div className="rounded-[26px] border border-amber-200 bg-amber-50 px-6 py-10 text-center">
                <h2 className="text-2xl font-bold tracking-[-0.03em] text-[var(--public-primary-hover)]">
                  Booking Baru Tidak Tersedia Untuk Tanggal Yang Sudah Lewat
                </h2>
                <p className="mx-auto mt-3 max-w-2xl text-sm leading-7 text-amber-800">
                  Anda tetap bisa melihat daftar booking di panel kanan, namun pengajuan baru hanya tersedia mulai hari ini.
                </p>
              </div>
            ) : (
              <>
                <div className="flex flex-col gap-4 border-b border-[var(--public-border)] pb-6 sm:flex-row sm:items-start sm:justify-between">
                  <div>
                    <h2 className="text-2xl font-bold tracking-[-0.03em] text-[var(--public-primary-hover)]">
                      Form Booking Multi-Ruangan
                    </h2>
                    <p className="mt-2 text-sm text-[var(--public-text-muted)]">
                      Satu pengajuan bisa memesan beberapa ruangan pada tanggal yang sama dengan jam berbeda per ruangan.
                    </p>
                  </div>
                  <div className="rounded-2xl bg-[var(--public-surface-soft)] px-4 py-3 text-sm text-[var(--public-text-muted)]">
                    <p>
                      <span className="font-semibold text-[var(--public-primary-hover)]">
                        Tanggal:
                      </span>{' '}
                      {selectedDateLabel}
                    </p>
                  </div>
                </div>

                {(errors.booking_slots || localConflicts.length > 0 || flash?.roomBookingConflicts?.length) ? (
                  <div className="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-700">
                    {errors.booking_slots ? <p>{errors.booking_slots}</p> : null}
                    {localConflicts.length > 0 ? (
                      <p>
                        Terdapat bentrok lokal pada slot: {localConflicts
                          .map(
                            (conflict) =>
                              `${conflict.roomLabel} (${conflict.startTime}-${conflict.endTime})`,
                          )
                          .join(', ')}.
                      </p>
                    ) : null}
                    {!errors.booking_slots && flash?.roomBookingConflicts?.length ? (
                      <p>
                        Konflik server: {flash.roomBookingConflicts
                          .map(
                            (conflict) =>
                              `${conflict.room_name} (${conflict.start_time}-${conflict.end_time})`,
                          )
                          .join(', ')}.
                      </p>
                    ) : null}
                  </div>
                ) : null}

                <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
                  <div className="grid gap-5 md:grid-cols-2">
                    <div>
                      <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                        Nama Mahasiswa
                        <RequiredMark />
                      </label>
                      <input
                        value={data.student_name}
                        onChange={(event) =>
                          setData('student_name', event.target.value)
                        }
                        type="text"
                        required
                        className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                      />
                      {errors.student_name ? (
                        <p className="mt-2 text-xs text-red-600">{errors.student_name}</p>
                      ) : null}
                    </div>

                    <div>
                      <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                        NIM
                        <RequiredMark />
                      </label>
                      <input
                        value={data.nim}
                        onChange={(event) => setData('nim', event.target.value)}
                        type="text"
                        required
                        className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                      />
                      {errors.nim ? <p className="mt-2 text-xs text-red-600">{errors.nim}</p> : null}
                    </div>

                    <div>
                      <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                        Program Studi
                        <RequiredMark />
                      </label>
                      <select
                        value={data.study_program}
                        onChange={(event) => setData('study_program', event.target.value)}
                        required
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
                        <p className="mt-2 text-xs text-red-600">{errors.study_program}</p>
                      ) : null}
                    </div>

                    <div>
                      <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                        Nomor WhatsApp
                        <RequiredMark />
                      </label>
                      <input
                        value={data.phone_number}
                        onChange={(event) => setData('phone_number', event.target.value)}
                        type="tel"
                        required
                        className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                      />
                      {errors.phone_number ? (
                        <p className="mt-2 text-xs text-red-600">{errors.phone_number}</p>
                      ) : null}
                    </div>

                    <div>
                      <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                        Unit / Organisasi
                        <RequiredMark />
                      </label>
                      <input
                        value={data.unit}
                        onChange={(event) => setData('unit', event.target.value)}
                        type="text"
                        required
                        className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                      />
                      {errors.unit ? <p className="mt-2 text-xs text-red-600">{errors.unit}</p> : null}
                    </div>

                    <div>
                      <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                        Jumlah Peserta
                        <RequiredMark />
                      </label>
                      <input
                        value={data.number_of_participants}
                        onChange={(event) => setData('number_of_participants', event.target.value)}
                        type="number"
                        min={1}
                        required
                        className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                      />
                      {errors.number_of_participants ? (
                        <p className="mt-2 text-xs text-red-600">{errors.number_of_participants}</p>
                      ) : null}
                    </div>
                  </div>

                  <div>
                    <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                      Nama Kegiatan
                      <RequiredMark />
                    </label>
                    <textarea
                      value={data.activity_name}
                      onChange={(event) => setData('activity_name', event.target.value)}
                      rows={3}
                      required
                      className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                    />
                    {errors.activity_name ? (
                      <p className="mt-2 text-xs text-red-600">{errors.activity_name}</p>
                    ) : null}
                  </div>

                  <div className="rounded-2xl border border-[var(--public-border)] bg-[var(--public-surface-soft)] p-4 sm:p-5">
                    <div className="flex items-center justify-between gap-3">
                      <p className="text-sm font-semibold text-[var(--public-primary-hover)]">
                        Slot Ruangan
                        <RequiredMark />
                      </p>
                      <button
                        type="button"
                        onClick={addSlotRow}
                        className="rounded-xl bg-[var(--public-primary-hover)] px-4 py-2 text-sm font-semibold text-white transition hover:brightness-95"
                      >
                        Tambah Slot
                      </button>
                    </div>

                    <div className="mt-4 space-y-3">
                      {data.booking_slots.map((slot, slotIndex) => (
                        <div
                          key={`slot-${slotIndex}`}
                          className="grid gap-3 rounded-2xl border border-[var(--public-border)] bg-white p-4 md:grid-cols-[1fr_0.8fr_0.8fr_auto]"
                        >
                          <div>
                            <label className="mb-2 block text-xs font-semibold uppercase tracking-[0.1em] text-[var(--public-text-muted)]">
                              Ruangan
                            </label>
                            <select
                              value={slot.room_id}
                              onChange={(event) =>
                                updateSlotRow(slotIndex, 'room_id', event.target.value)
                              }
                              required
                              className="w-full rounded-xl border border-[var(--public-border)] px-4 py-2 text-sm"
                            >
                              <option value="">Pilih Ruangan</option>
                              {rooms.map((room) => (
                                <option key={room.id} value={room.id}>
                                  {room.name} (Kapasitas {room.capacity})
                                </option>
                              ))}
                            </select>
                            {formErrors[`booking_slots.${slotIndex}.room_id`] ? (
                              <p className="mt-2 text-xs text-red-600">
                                {formErrors[`booking_slots.${slotIndex}.room_id`]}
                              </p>
                            ) : null}
                          </div>

                          <div>
                            <label className="mb-2 block text-xs font-semibold uppercase tracking-[0.1em] text-[var(--public-text-muted)]">
                              Jam Mulai
                            </label>
                            <input
                              value={slot.start_time}
                              onChange={(event) =>
                                updateSlotRow(slotIndex, 'start_time', event.target.value)
                              }
                              type="time"
                              required
                              className="w-full rounded-xl border border-[var(--public-border)] px-4 py-2 text-sm"
                            />
                            {formErrors[`booking_slots.${slotIndex}.start_time`] ? (
                              <p className="mt-2 text-xs text-red-600">
                                {formErrors[`booking_slots.${slotIndex}.start_time`]}
                              </p>
                            ) : null}
                          </div>

                          <div>
                            <label className="mb-2 block text-xs font-semibold uppercase tracking-[0.1em] text-[var(--public-text-muted)]">
                              Jam Selesai
                            </label>
                            <input
                              value={slot.end_time}
                              onChange={(event) =>
                                updateSlotRow(slotIndex, 'end_time', event.target.value)
                              }
                              type="time"
                              required
                              className="w-full rounded-xl border border-[var(--public-border)] px-4 py-2 text-sm"
                            />
                            {formErrors[`booking_slots.${slotIndex}.end_time`] ? (
                              <p className="mt-2 text-xs text-red-600">
                                {formErrors[`booking_slots.${slotIndex}.end_time`]}
                              </p>
                            ) : null}
                          </div>

                          <div className="flex items-end">
                            <button
                              type="button"
                              onClick={() => removeSlotRow(slotIndex)}
                              className="w-full rounded-xl border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50"
                              disabled={data.booking_slots.length <= 1}
                            >
                              Hapus
                            </button>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>

                  <div>
                    <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                      Dokumen Pendukung
                      <RequiredMark />
                    </label>
                    <input
                      onChange={(event) =>
                        setData('document', event.target.files?.[0] ?? null)
                      }
                      type="file"
                      accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                      required
                      className="w-full rounded-2xl border border-[var(--public-border)] bg-white px-5 py-3 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-[var(--public-primary-hover)] file:px-4 file:py-2 file:text-white"
                    />
                    {errors.document ? (
                      <p className="mt-2 text-xs text-red-600">{errors.document}</p>
                    ) : null}
                  </div>

                  <div className="flex justify-end">
                    <button
                      type="submit"
                      disabled={processing || localConflicts.length > 0}
                      className="rounded-2xl bg-[var(--public-accent)] px-10 py-4 text-base font-semibold text-[var(--public-primary-hover)] shadow-[0_10px_22px_rgba(244,196,48,0.28)] transition hover:brightness-95 disabled:cursor-not-allowed disabled:opacity-70"
                    >
                      {processing ? 'Mengirim...' : 'Ajukan Peminjaman'}
                    </button>
                  </div>
                </form>
              </>
            )}
          </div>
        </div>
      </section>
    </AppLayout>
  );
};

export default RoomBooking;
