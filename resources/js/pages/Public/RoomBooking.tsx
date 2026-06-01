import { useForm, usePage } from '@inertiajs/react';
import {
  format,
  isBefore,
  isValid,
  parseISO,
  startOfDay,
  startOfMonth,
} from 'date-fns';
import type { FormEvent } from 'react';
import { useEffect, useMemo, useState } from 'react';

import BookingCalendar from '@/components/BookingCalendar/BookingCalendar';
import AppLayout from '@/components/Layout/AppLayout/AppLayout';
import type { BookingCalendarEvent } from '@/types/Booking';
import type {
  RoomBookingProps,
  RoomBookingSharedPageProps,
} from '@/types/pages/Public/RoomBooking';
import {
  preventNonNumericKeydown,
  sanitizeNumericValue,
} from '@/utils/NumericInput';

type BookingSlotForm = {
  room_id: string;
  start_time: string;
  end_time: string;
};

type LocalBookingConflict = {
  bookingDate: string;
  index: number;
  roomLabel: string;
  startTime: string;
  endTime: string;
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

const normalizeDateValue = (value: string): string | null => {
  if (!value) {
    return null;
  }

  const parsedDate = parseISO(value);

  if (!isValid(parsedDate)) {
    return null;
  }

  return format(parsedDate, 'yyyy-MM-dd');
};

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
  const [isFormModalOpen, setIsFormModalOpen] = useState(false);
  const [toastMessage, setToastMessage] = useState<string | null>(null);
  const [bookingsByDate, setBookingsByDate] = useState<Record<string, BookingCalendarEvent[]>>(
    {},
  );
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
    is_recurring: false,
    repeat_dates: [] as string[],
    selected_date: '',
    booking_slots: [{ room_id: '', start_time: '', end_time: '' }] as BookingSlotForm[],
    document: null as File | null,
  });
  const formErrors = errors as Record<string, string | undefined>;
  const selectedDateValue = selectedDate ? format(selectedDate, 'yyyy-MM-dd') : '';

  const bookingDates = useMemo(() => {
    if (!selectedDateValue) {
      return [];
    }

    const repeatDates = data.is_recurring
      ? data.repeat_dates
          .map((value) => normalizeDateValue(value))
          .filter((value): value is string => Boolean(value))
      : [];

    return Array.from(new Set([selectedDateValue, ...repeatDates])).sort();
  }, [data.is_recurring, data.repeat_dates, selectedDateValue]);

  const dayBookings = selectedDateValue ? bookingsByDate[selectedDateValue] ?? [] : [];

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
      setDayBookingsError(null);
      setIsDayBookingsLoading(false);
      setData('selected_date', '');

      return;
    }

    setData('selected_date', selectedDateValue);
  }, [selectedDate, selectedDateValue, setData]);

  useEffect(() => {
    if (!selectedDateValue) {
      return;
    }

    const datesToFetch = bookingDates.filter((date) => !(date in bookingsByDate));

    if (datesToFetch.length === 0) {
      setDayBookingsError(null);
      setIsDayBookingsLoading(false);

      return;
    }

    const controller = new AbortController();
    const shouldTrackSelectedDate = datesToFetch.includes(selectedDateValue);

    if (shouldTrackSelectedDate) {
      setIsDayBookingsLoading(true);
      setDayBookingsError(null);
    }

    Promise.allSettled(
      datesToFetch.map(async (date) => {
        const response = await window.fetch(`/booking/bookings?selected_date=${date}`, {
          signal: controller.signal,
          headers: {
            Accept: 'application/json',
          },
        });

        if (!response.ok) {
          throw new Error('Gagal memuat jadwal ruangan.');
        }

        const payload = (await response.json()) as {
          data?: BookingCalendarEvent[];
        };

        return {
          date,
          data: payload.data ?? [],
        };
      }),
    )
      .then((results) => {
        if (controller.signal.aborted) {
          return;
        }

        const nextBookings: Record<string, BookingCalendarEvent[]> = {};
        let selectedDateFailed = false;

        results.forEach((result, index) => {
          const date = datesToFetch[index];

          if (result.status === 'fulfilled') {
            nextBookings[date] = result.value.data;
            return;
          }

          if (date === selectedDateValue) {
            selectedDateFailed = true;
          }
        });

        if (Object.keys(nextBookings).length > 0) {
          setBookingsByDate((previous) => ({
            ...previous,
            ...nextBookings,
          }));
        }

        setDayBookingsError(selectedDateFailed ? 'Jadwal ruangan belum berhasil dimuat.' : null);
      })
      .catch((error: unknown) => {
        if (error instanceof DOMException && error.name === 'AbortError') {
          return;
        }

        if (shouldTrackSelectedDate) {
          setDayBookingsError('Jadwal ruangan belum berhasil dimuat.');
        }
      })
      .finally(() => {
        if (!controller.signal.aborted && shouldTrackSelectedDate) {
          setIsDayBookingsLoading(false);
        }
      });

    return () => controller.abort();
  }, [bookingDates, bookingsByDate, selectedDateValue]);

  const selectedDateLabel = selectedDate
    ? format(selectedDate, 'dd MMMM yyyy')
    : 'Belum memilih tanggal';
  const isPastSelectedDate = selectedDate
    ? isBefore(startOfDay(selectedDate), startOfDay(new Date()))
    : false;

  const handleDateChange = (date: Date | undefined) => {
    setSelectedDate(date);
    setIsFormModalOpen(false);
  };

  const addRepeatDateRow = () => {
    setData('repeat_dates', [...data.repeat_dates, '']);
  };

  const removeRepeatDateRow = (index: number) => {
    setData(
      'repeat_dates',
      data.repeat_dates.filter((_, repeatIndex) => repeatIndex !== index),
    );
  };

  const updateRepeatDateRow = (index: number, value: string) => {
    setData(
      'repeat_dates',
      data.repeat_dates.map((date, repeatIndex) => (repeatIndex === index ? value : date)),
    );
  };

  const toggleRecurring = (checked: boolean) => {
    setData('is_recurring', checked);
    setData('repeat_dates', checked ? (data.repeat_dates.length > 0 ? data.repeat_dates : ['']) : []);
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

  const repeatDateErrors = useMemo(() => {
    if (!data.is_recurring) {
      return [] as string[];
    }

    const messages: string[] = [];
    const seenDates = new Set<string>();
    const today = startOfDay(new Date());

    if (data.repeat_dates.length === 0) {
      messages.push('Tambahkan minimal satu tanggal tambahan untuk booking berulang.');
    }

    data.repeat_dates.forEach((value) => {
      const normalizedDate = normalizeDateValue(value);

      if (!normalizedDate) {
        messages.push('Semua tanggal tambahan wajib diisi.');
        return;
      }

      const parsedDate = parseISO(normalizedDate);

      if (isBefore(startOfDay(parsedDate), today)) {
        messages.push('Tanggal tambahan tidak boleh sebelum hari ini.');
      }

      if (normalizedDate === selectedDateValue) {
        messages.push('Tanggal tambahan tidak boleh sama dengan tanggal utama.');
      }

      if (seenDates.has(normalizedDate)) {
        messages.push('Tanggal tambahan duplikat tidak diperbolehkan.');
      }

      seenDates.add(normalizedDate);
    });

    return Array.from(new Set(messages));
  }, [data.is_recurring, data.repeat_dates, selectedDateValue]);

  const localConflicts = useMemo(() => {
    const conflicts: LocalBookingConflict[] = [];

    bookingDates.forEach((bookingDate) => {
      const bookingsForDate = bookingsByDate[bookingDate] ?? [];

      data.booking_slots.forEach((slot, index) => {
        if (!slot.room_id || !slot.start_time || !slot.end_time) {
          return;
        }

        const matched = bookingsForDate.find((booking) => {
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
          return;
        }

        const roomLabel = rooms.find((room) => String(room.id) === slot.room_id)?.name;

        conflicts.push({
          bookingDate,
          index,
          roomLabel: roomLabel ?? 'Ruang',
          startTime: slot.start_time,
          endTime: slot.end_time,
        });
      });
    });

    return conflicts;
  }, [bookingDates, bookingsByDate, data.booking_slots, rooms]);

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    if (!selectedDate) {
      return;
    }

    if (repeatDateErrors.length > 0 || localConflicts.length > 0) {
      return;
    }

    post('/booking', {
      forceFormData: true,
      onSuccess: () => {
        reset();
        setData('is_recurring', false);
        setData('repeat_dates', []);
        setData('booking_slots', [{ room_id: '', start_time: '', end_time: '' }]);
        setIsFormModalOpen(false);
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
            Peminjaman Ruang 
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

            <div className="public-card flex h-full flex-col p-5">
              <p className="text-[11px] font-semibold uppercase tracking-[0.2em] text-[var(--public-text-muted)]">
                Booking Hari Terpilih
              </p>
              <div className="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p className="text-lg font-semibold text-[var(--public-primary-hover)]">
                  {selectedDateLabel}
                </p>
                <button
                  type="button"
                  onClick={() => setIsFormModalOpen(true)}
                  disabled={!selectedDate || isPastSelectedDate}
                  className="rounded-xl bg-[var(--public-accent)] px-5 py-2 text-sm font-semibold text-[var(--public-primary-hover)] transition hover:brightness-95 disabled:cursor-not-allowed disabled:opacity-60"
                >
                  Ajukan
                </button>
              </div>

              {dayBookingsError ? (
                <p className="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                  {dayBookingsError}
                </p>
              ) : null}

              {selectedDate && isPastSelectedDate ? (
                <p className="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                  Pengajuan baru hanya tersedia mulai hari ini.
                </p>
              ) : null}

              <div className="mt-4 min-h-0 flex-1 space-y-3 overflow-y-auto pr-1 xl:max-h-[560px]">
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
                      className="rounded-2xl border border-[var(--public-border)] bg-[var(--public-surface-soft)] px-4 py-3"
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

      {isFormModalOpen && selectedDate && !isPastSelectedDate ? (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/50 px-4 py-8">
          <div className="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-3xl bg-white p-5 shadow-2xl sm:p-6">
            <div className="flex items-start justify-between gap-4 border-b border-[var(--public-border)] pb-5">
              <div>
                <h2 className="text-xl font-bold tracking-[-0.03em] text-[var(--public-primary-hover)] sm:text-2xl">
                  Form Booking Multi-Ruangan
                </h2>
                <p className="mt-2 text-sm text-[var(--public-text-muted)]">
                  Satu pengajuan bisa memesan beberapa ruangan dan mengulang slot yang sama ke beberapa tanggal berbeda.
                </p>
              </div>
              <button
                type="button"
                onClick={() => setIsFormModalOpen(false)}
                className="rounded-full p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                aria-label="Tutup form booking"
              >
                <svg viewBox="0 0 20 20" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8">
                  <path d="m5 5 10 10M15 5 5 15" />
                </svg>
              </button>
            </div>

            {(errors.booking_slots ||
              errors.repeat_dates ||
              repeatDateErrors.length > 0 ||
              localConflicts.length > 0 ||
              flash?.roomBookingConflicts?.length) ? (
              <div className="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-700">
                {errors.booking_slots ? <p>{errors.booking_slots}</p> : null}
                {errors.repeat_dates ? <p>{errors.repeat_dates}</p> : null}
                {repeatDateErrors.map((message) => (
                  <p key={message}>{message}</p>
                ))}
                {localConflicts.length > 0 ? (
                  <p>
                    Terdapat bentrok lokal pada slot: {localConflicts
                      .map(
                        (conflict) =>
                          `${format(parseISO(conflict.bookingDate), 'dd MMM yyyy')} - ${conflict.roomLabel} (${conflict.startTime}-${conflict.endTime})`,
                      )
                      .join(', ')}.
                  </p>
                ) : null}
                {!errors.booking_slots && flash?.roomBookingConflicts?.length ? (
                  <p>
                    Konflik server: {flash.roomBookingConflicts
                      .map(
                        (conflict) =>
                          `${format(parseISO(conflict.booking_date), 'dd MMM yyyy')} - ${conflict.room_name} (${conflict.start_time}-${conflict.end_time})`,
                      )
                      .join(', ')}.
                  </p>
                ) : null}
              </div>
            ) : null}

            <form className="mt-6 space-y-5" onSubmit={handleSubmit}>
              <div className="grid gap-4 md:grid-cols-2">
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
                    className="w-full rounded-2xl border border-[var(--public-border)] px-4 py-2.5 text-sm"
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
                    className="w-full rounded-2xl border border-[var(--public-border)] px-4 py-2.5 text-sm"
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
                    className="w-full rounded-2xl border border-[var(--public-border)] px-4 py-2.5 text-sm"
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
                    onChange={(event) =>
                      setData('phone_number', sanitizeNumericValue(event.target.value))
                    }
                    type="tel"
                    inputMode="numeric"
                    pattern="[0-9]*"
                    onKeyDown={preventNonNumericKeydown}
                    required
                    className="w-full rounded-2xl border border-[var(--public-border)] px-4 py-2.5 text-sm"
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
                    className="w-full rounded-2xl border border-[var(--public-border)] px-4 py-2.5 text-sm"
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
                    className="w-full rounded-2xl border border-[var(--public-border)] px-4 py-2.5 text-sm"
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
                  rows={2}
                  required
                  className="w-full rounded-2xl border border-[var(--public-border)] px-4 py-2.5 text-sm"
                />
                {errors.activity_name ? (
                  <p className="mt-2 text-xs text-red-600">{errors.activity_name}</p>
                ) : null}
              </div>

              <div className="rounded-2xl border border-[var(--public-border)] bg-[var(--public-surface-soft)] p-4">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                  <div>
                    <p className="text-sm font-semibold text-[var(--public-primary-hover)]">
                      Pengaturan Tanggal
                    </p>
                    <p className="mt-1 text-xs text-[var(--public-text-muted)]">
                      Tanggal utama mengikuti pilihan kalender. Aktifkan booking berulang untuk menambah hari lain dengan slot ruangan yang sama.
                    </p>
                  </div>
                  <label className="inline-flex items-center gap-3 rounded-2xl border border-[var(--public-border)] bg-white px-4 py-3 text-sm font-medium text-[var(--public-primary-hover)]">
                    <input
                      type="checkbox"
                      checked={data.is_recurring}
                      onChange={(event) => toggleRecurring(event.target.checked)}
                      className="h-4 w-4 rounded border-[var(--public-border)] text-[var(--public-primary-hover)]"
                    />
                    Berulang
                  </label>
                </div>

                <div className="mt-4 rounded-2xl border border-dashed border-[var(--public-border)] bg-white px-4 py-3">
                  <p className="text-xs font-semibold uppercase tracking-[0.12em] text-[var(--public-text-muted)]">
                    Tanggal Utama
                  </p>
                  <p className="mt-1 text-sm font-semibold text-[var(--public-primary-hover)]">
                    {selectedDateLabel}
                  </p>
                </div>

                {data.is_recurring ? (
                  <div className="mt-4 space-y-3">
                    <div className="flex items-center justify-between gap-3">
                      <p className="text-sm font-semibold text-[var(--public-primary-hover)]">
                        Tanggal Tambahan
                        <RequiredMark />
                      </p>
                      <button
                        type="button"
                        onClick={addRepeatDateRow}
                        className="rounded-xl bg-[var(--public-primary-hover)] px-4 py-2 text-sm font-semibold text-white transition hover:brightness-95"
                      >
                        Tambah Tanggal
                      </button>
                    </div>

                    {data.repeat_dates.map((repeatDate, repeatIndex) => (
                      <div
                        key={`repeat-date-${repeatIndex}`}
                        className="grid gap-3 rounded-2xl border border-[var(--public-border)] bg-white p-4 md:grid-cols-[1fr_auto]"
                      >
                        <div>
                          <label className="mb-2 block text-xs font-semibold uppercase tracking-[0.1em] text-[var(--public-text-muted)]">
                            Tanggal Tambahan {repeatIndex + 1}
                          </label>
                          <input
                            value={repeatDate}
                            onChange={(event) => updateRepeatDateRow(repeatIndex, event.target.value)}
                            type="date"
                            min={format(new Date(), 'yyyy-MM-dd')}
                            required={data.is_recurring}
                            className="w-full rounded-xl border border-[var(--public-border)] px-4 py-2 text-sm"
                          />
                          {formErrors[`repeat_dates.${repeatIndex}`] ? (
                            <p className="mt-2 text-xs text-red-600">
                              {formErrors[`repeat_dates.${repeatIndex}`]}
                            </p>
                          ) : null}
                        </div>

                        <div className="flex items-end">
                          <button
                            type="button"
                            onClick={() => removeRepeatDateRow(repeatIndex)}
                            className="w-full rounded-xl border border-red-200 px-4 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-50"
                            disabled={data.repeat_dates.length <= 1}
                          >
                            Hapus
                          </button>
                        </div>
                      </div>
                    ))}
                  </div>
                ) : null}

                <div className="mt-4 rounded-2xl border border-[var(--public-border)] bg-white px-4 py-3">
                  <p className="text-xs font-semibold uppercase tracking-[0.12em] text-[var(--public-text-muted)]">
                    Tanggal Yang Akan Dibooking
                  </p>
                  <p className="mt-2 text-sm text-[var(--public-text-muted)]">
                    {bookingDates.length > 0
                      ? bookingDates
                          .map((date) => format(parseISO(date), 'dd MMMM yyyy'))
                          .join(', ')
                      : 'Belum ada tanggal terpilih.'}
                  </p>
                </div>
              </div>

              <div className="rounded-2xl border border-[var(--public-border)] bg-[var(--public-surface-soft)] p-4">
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
                  className="w-full rounded-2xl border border-[var(--public-border)] bg-white px-4 py-2.5 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-[var(--public-primary-hover)] file:px-4 file:py-2 file:text-white"
                />
                {errors.document ? (
                  <p className="mt-2 text-xs text-red-600">{errors.document}</p>
                ) : null}
              </div>

              <div className="flex justify-end gap-3">
                <button
                  type="button"
                  onClick={() => setIsFormModalOpen(false)}
                  className="rounded-2xl border border-[var(--public-border)] px-6 py-3 text-sm font-semibold text-[var(--public-text-muted)] transition hover:bg-[var(--public-surface-soft)]"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  disabled={processing || repeatDateErrors.length > 0 || localConflicts.length > 0}
                  className="rounded-2xl bg-[var(--public-accent)] px-10 py-4 text-base font-semibold text-[var(--public-primary-hover)] shadow-[0_10px_22px_rgba(244,196,48,0.28)] transition hover:brightness-95 disabled:cursor-not-allowed disabled:opacity-70"
                >
                  {processing ? 'Mengirim...' : 'Ajukan Peminjaman'}
                </button>
              </div>
            </form>
          </div>
        </div>
      ) : null}
    </AppLayout>
  );
};

export default RoomBooking;
