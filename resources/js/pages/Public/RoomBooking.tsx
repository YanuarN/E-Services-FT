import { router, useForm, usePage } from '@inertiajs/react';
import { format, startOfMonth } from 'date-fns';
import { FormEvent, useMemo, useState } from 'react';

import BookingCalendar from '@/components/BookingCalendar/BookingCalendar';
import InfoCard from '@/components/InfoCard/InfoCard';
import AppLayout from '@/components/Layout/AppLayout/AppLayout';
import StepProgress from '@/components/StepProgress/StepProgress';
import { BookingSteps } from '@/data/PublicContent';
import type {
  RoomBookingProps,
  RoomBookingSharedPageProps,
} from '@/types/pages/Public/RoomBooking';
import {
  buildAvailabilityMap,
  findEventsForDate,
} from '@/utils/BookingAvailability';

const RoomBooking = ({ rooms, bookings, studyPrograms }: RoomBookingProps) => {
  const [month, setMonth] = useState(startOfMonth(new Date()));
  const [selectedDate, setSelectedDate] = useState<Date | undefined>(new Date());
  const { flash } = usePage<RoomBookingSharedPageProps>().props;

  const { data, setData, processing, errors, reset } = useForm({
    student_name: '',
    nim: '',
    study_program: '',
    phone_number: '',
    unit: '',
    activity_name: '',
    room_id: '',
    number_of_participants: '',
    selected_date: format(new Date(), 'yyyy-MM-dd'),
    start_time: '',
    end_time: '',
    document: null as File | null,
  });

  const availabilityMap = useMemo(
    () => buildAvailabilityMap(bookings),
    [bookings],
  );
  const selectedDayEvents = useMemo(
    () => (selectedDate ? findEventsForDate(selectedDate, bookings) : []),
    [bookings, selectedDate],
  );

  const selectedRoom = useMemo(
    () => rooms.find((room) => String(room.id) === data.room_id),
    [data.room_id, rooms],
  );

  const handleDateChange = (date: Date | undefined) => {
    setSelectedDate(date);
    setData('selected_date', date ? format(date, 'yyyy-MM-dd') : '');
  };

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();

    router.post('/booking', data, {
      forceFormData: true,
      onSuccess: () => {
        reset('activity_name', 'start_time', 'end_time', 'document');
      },
    });
  };

  return (
    <AppLayout currentPath="/booking" pageTitle="Peminjaman Ruang">
      <section className="py-8 sm:py-10">
        <div className="public-container">
          <h1 className="text-4xl font-bold tracking-[-0.05em] text-[var(--public-primary-hover)] sm:text-5xl">
            Peminjaman Ruang
          </h1>
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
            />

            <div className="space-y-5">
              <InfoCard title="Informasi Penting">
                <ul className="space-y-3 text-[15px] leading-7">
                  <li>Pastikan memilih tanggal terlebih dahulu di kalender.</li>
                  <li>Mahasiswa dapat menentukan jam mulai dan jam selesai sendiri.</li>
                  <li>Status pengajuan dan keputusan admin akan dikirim via WhatsApp.</li>
                </ul>
              </InfoCard>

              <div className="rounded-[24px] border border-[rgba(31,42,102,0.12)] bg-[var(--public-primary-hover)] p-6 text-white shadow-[var(--public-shadow)]">
                <p className="text-white/56 text-[11px] font-semibold uppercase tracking-[0.22em]">
                  Ringkasan Tanggal
                </p>
                <p className="mt-1 text-[1.7rem] font-bold tracking-[-0.04em] text-white">
                  {selectedDate ? format(selectedDate, 'dd MMM yyyy') : '-'}
                </p>
                <div className="mt-5 h-px bg-white/20" />
                <p className="mt-5 text-sm text-white/80">
                  {selectedDayEvents.length > 0
                    ? `${selectedDayEvents.length} booking aktif pada tanggal ini`
                    : 'Belum ada booking aktif pada tanggal ini'}
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section className="py-12">
        <div className="public-container">
          <div className="public-card p-6 sm:p-8">
            {flash?.success ? (
              <div className="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {flash.success}
              </div>
            ) : null}

            <h2 className="text-2xl font-bold tracking-[-0.03em] text-[var(--public-primary-hover)]">
              Form Booking Lab / Ruang
            </h2>
            <p className="mt-2 text-sm text-[var(--public-text-muted)]">
              Pilih tanggal dari kalender, lalu isi jam penggunaan sesuai kebutuhan.
            </p>

            <form className="mt-8 space-y-6" onSubmit={handleSubmit}>
              <div className="grid gap-5 md:grid-cols-2">
                <div>
                  <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                    Nama Mahasiswa
                  </label>
                  <input
                    value={data.student_name}
                    onChange={(event) => setData('student_name', event.target.value)}
                    type="text"
                    className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                  />
                  {errors.student_name ? <p className="mt-2 text-xs text-red-600">{errors.student_name}</p> : null}
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
                  {errors.nim ? <p className="mt-2 text-xs text-red-600">{errors.nim}</p> : null}
                </div>

                <div>
                  <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                    Program Studi
                  </label>
                  <select
                    value={data.study_program}
                    onChange={(event) => setData('study_program', event.target.value)}
                    className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                  >
                    <option value="">Pilih Program Studi</option>
                    {studyPrograms.map((program) => (
                      <option key={program} value={program}>
                        {program}
                      </option>
                    ))}
                  </select>
                  {errors.study_program ? <p className="mt-2 text-xs text-red-600">{errors.study_program}</p> : null}
                </div>

                <div>
                  <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                    Nomor WhatsApp
                  </label>
                  <input
                    value={data.phone_number}
                    onChange={(event) => setData('phone_number', event.target.value)}
                    type="tel"
                    className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                  />
                  {errors.phone_number ? <p className="mt-2 text-xs text-red-600">{errors.phone_number}</p> : null}
                </div>

                <div>
                  <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                    Unit / Organisasi
                  </label>
                  <input
                    value={data.unit}
                    onChange={(event) => setData('unit', event.target.value)}
                    type="text"
                    className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                  />
                  {errors.unit ? <p className="mt-2 text-xs text-red-600">{errors.unit}</p> : null}
                </div>

                <div>
                  <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                    Ruangan
                  </label>
                  <select
                    value={data.room_id}
                    onChange={(event) => setData('room_id', event.target.value)}
                    className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                  >
                    <option value="">Pilih Ruangan</option>
                    {rooms.map((room) => (
                      <option key={room.id} value={room.id}>
                        {room.name} (Kapasitas {room.capacity})
                      </option>
                    ))}
                  </select>
                  {errors.room_id ? <p className="mt-2 text-xs text-red-600">{errors.room_id}</p> : null}
                </div>

                <div>
                  <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                    Tanggal
                  </label>
                  <input
                    value={data.selected_date}
                    onChange={(event) => setData('selected_date', event.target.value)}
                    type="date"
                    className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                  />
                  {errors.selected_date ? <p className="mt-2 text-xs text-red-600">{errors.selected_date}</p> : null}
                </div>

                <div>
                  <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                    Jumlah Peserta
                  </label>
                  <input
                    value={data.number_of_participants}
                    onChange={(event) => setData('number_of_participants', event.target.value)}
                    type="number"
                    min={1}
                    className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                  />
                  {errors.number_of_participants ? <p className="mt-2 text-xs text-red-600">{errors.number_of_participants}</p> : null}
                </div>

                <div>
                  <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                    Jam Mulai
                  </label>
                  <input
                    value={data.start_time}
                    onChange={(event) => setData('start_time', event.target.value)}
                    type="time"
                    className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                  />
                  {errors.start_time ? <p className="mt-2 text-xs text-red-600">{errors.start_time}</p> : null}
                </div>

                <div>
                  <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                    Jam Selesai
                  </label>
                  <input
                    value={data.end_time}
                    onChange={(event) => setData('end_time', event.target.value)}
                    type="time"
                    className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                  />
                  {errors.end_time ? <p className="mt-2 text-xs text-red-600">{errors.end_time}</p> : null}
                </div>
              </div>

              <div>
                <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                  Nama Kegiatan
                </label>
                <textarea
                  value={data.activity_name}
                  onChange={(event) => setData('activity_name', event.target.value)}
                  rows={3}
                  className="w-full rounded-2xl border border-[var(--public-border)] px-5 py-3 text-sm"
                />
                {errors.activity_name ? <p className="mt-2 text-xs text-red-600">{errors.activity_name}</p> : null}
              </div>

              <div>
                <label className="mb-2 block text-sm font-medium text-[var(--public-text-muted)]">
                  Dokumen Pendukung
                </label>
                <input
                  onChange={(event) => setData('document', event.target.files?.[0] ?? null)}
                  type="file"
                  accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                  className="w-full rounded-2xl border border-[var(--public-border)] bg-white px-5 py-3 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-[var(--public-primary-hover)] file:px-4 file:py-2 file:text-white"
                />
                {errors.document ? <p className="mt-2 text-xs text-red-600">{errors.document}</p> : null}
              </div>

              {selectedRoom ? (
                <div className="rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                  Ruang dipilih: <strong>{selectedRoom.name}</strong> (kapasitas {selectedRoom.capacity}).
                </div>
              ) : null}

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
          </div>
        </div>
      </section>
    </AppLayout>
  );
};

export default RoomBooking;
