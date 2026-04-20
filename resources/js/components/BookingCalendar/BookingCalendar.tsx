import type { DatesSetArg, DayCellContentArg } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import type { DateClickArg } from '@fullcalendar/interaction';
import interactionPlugin from '@fullcalendar/interaction';
import FullCalendar from '@fullcalendar/react';
import {
  format,
  isBefore,
  isSameDay,
  isSameMonth,
  startOfDay,
  startOfMonth,
} from 'date-fns';
import { useEffect, useMemo, useRef } from 'react';

import type { BookingCalendarProps } from '@/types/components/BookingCalendar';
import { getAvailabilityForDate } from '@/utils/BookingAvailability';

const DAY_LABELS = ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'];

const BookingCalendar = ({
  selectedDate,
  month,
  onSelectDate,
  onMonthChange,
  availabilityMap,
  isRoomSelected,
  selectedRoomLabel,
}: BookingCalendarProps) => {
  const calendarRef = useRef<FullCalendar | null>(null);
  const today = useMemo(() => startOfDay(new Date()), []);

  const selectedKey = selectedDate ? format(selectedDate, 'yyyy-MM-dd') : null;
  const renderedMonth = useMemo(() => format(month, 'MMMM yyyy'), [month]);

  useEffect(() => {
    const calendarApi = calendarRef.current?.getApi();

    if (!calendarApi) {
      return;
    }

    const activeDate = calendarApi.getDate();

    if (!isSameMonth(activeDate, month)) {
      calendarApi.gotoDate(month);
    }
  }, [month]);

  const handleMoveMonth = (direction: 'prev' | 'next') => {
    const calendarApi = calendarRef.current?.getApi();

    if (!calendarApi) {
      return;
    }

    if (direction === 'prev') {
      calendarApi.prev();
      return;
    }

    calendarApi.next();
  };

  const handleDatesSet = ({ view }: DatesSetArg) => {
    const nextMonth = startOfMonth(view.calendar.getDate());

    if (!isSameMonth(nextMonth, month)) {
      onMonthChange(nextMonth);
    }
  };

  const handleDateClick = ({ date }: DateClickArg) => {
    if (!isRoomSelected || isBefore(startOfDay(date), today)) {
      return;
    }

    onSelectDate(date);
  };

  const dayCellClassNames = ({ date, view }: DayCellContentArg) => {
    const isOutside = !isSameMonth(date, view.currentStart);
    const isPastDate = isBefore(startOfDay(date), today);

    if (!isRoomSelected) {
      return [
        'booking-fc-day',
        'booking-fc-day-idle',
        isOutside ? 'booking-fc-day-outside' : '',
      ].filter(Boolean);
    }

    const availability = getAvailabilityForDate(date, availabilityMap);
    const isSelected = selectedDate ? isSameDay(date, selectedDate) : false;

    return [
      'booking-fc-day',
      availability
        ? `booking-fc-day-${availability.status}`
        : 'booking-fc-day-available',
      isPastDate ? 'booking-fc-day-disabled' : '',
      isSelected ? 'booking-fc-day-selected' : '',
      isOutside ? 'booking-fc-day-outside' : '',
    ].filter(Boolean);
  };

  const renderDayCellContent = ({ date, view }: DayCellContentArg) => {
    const availability = getAvailabilityForDate(date, availabilityMap);
    const isSelected = selectedKey === format(date, 'yyyy-MM-dd');
    const isOutside = !isSameMonth(date, view.currentStart);

    let statusLabel = '';

    if (isRoomSelected) {
      if (isSelected) {
        statusLabel = 'PILIHAN ANDA';
      } else if (availability?.status === 'full') {
        statusLabel = 'PENUH';
      } else if (availability?.status === 'partial') {
        statusLabel = 'TERSEDIA SEBAGIAN';
      }
    }

    return (
      <div
        className={`booking-fc-day-inner ${isOutside ? 'booking-fc-day-inner-outside' : ''}`}
      >
        <span className="booking-fc-day-number">{date.getDate()}</span>
        {statusLabel ? (
          <span className="booking-fc-day-status">{statusLabel}</span>
        ) : null}
      </div>
    );
  };

  return (
    <div className="public-card overflow-hidden p-4 sm:p-5 lg:p-6">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <h2 className="text-[1.9rem] font-bold tracking-[-0.04em] text-[var(--public-primary-hover)] sm:text-[2.1rem]">
            {renderedMonth}
          </h2>
          <p className="mt-1 text-sm text-[var(--public-text-muted)]">
            {isRoomSelected
              ? `Klik tanggal untuk melihat booking ${selectedRoomLabel ?? 'ruangan ini'}`
              : 'Pilih ruangan terlebih dahulu agar warna ketersediaan tampil.'}
          </p>
        </div>

        <div className="flex items-center gap-2 self-start">
          <button
            type="button"
            aria-label="Bulan sebelumnya"
            onClick={() => handleMoveMonth('prev')}
            className="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-[var(--public-border)] bg-white text-[var(--public-primary-hover)] transition hover:bg-[var(--public-surface-soft)]"
          >
            <svg
              viewBox="0 0 20 20"
              className="h-4 w-4"
              fill="none"
              stroke="currentColor"
              strokeWidth="1.8"
            >
              <path d="M12 4 6 10l6 6" />
            </svg>
          </button>
          <button
            type="button"
            aria-label="Bulan berikutnya"
            onClick={() => handleMoveMonth('next')}
            className="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-[var(--public-border)] bg-white text-[var(--public-primary-hover)] transition hover:bg-[var(--public-surface-soft)]"
          >
            <svg
              viewBox="0 0 20 20"
              className="h-4 w-4"
              fill="none"
              stroke="currentColor"
              strokeWidth="1.8"
            >
              <path d="m8 4 6 6-6 6" />
            </svg>
          </button>
        </div>
      </div>

      <div className="booking-fc-shell mt-6 overflow-hidden rounded-[20px] border border-[var(--public-border)] bg-white">
        <FullCalendar
          ref={calendarRef}
          plugins={[dayGridPlugin, interactionPlugin]}
          initialView="dayGridMonth"
          initialDate={month}
          headerToolbar={false}
          fixedWeekCount
          showNonCurrentDates
          dayMaxEvents={false}
          height="auto"
          dateClick={handleDateClick}
          datesSet={handleDatesSet}
          dayHeaderContent={(arg) => DAY_LABELS[arg.date.getDay()]}
          dayCellClassNames={dayCellClassNames}
          dayCellContent={renderDayCellContent}
        />
      </div>

      {isRoomSelected ? (
        <div className="mt-5 flex flex-wrap items-center gap-x-5 gap-y-3 text-sm text-[var(--public-text-muted)]">
          <div className="flex items-center gap-2">
            <span className="h-4 w-4 rounded border border-[var(--public-border)] bg-white" />
            <span>Tersedia</span>
          </div>
          <div className="flex items-center gap-2">
            <span className="h-4 w-4 rounded bg-[#fff2a9]" />
            <span>Tersedia Sebagian</span>
          </div>
          <div className="flex items-center gap-2">
            <span className="h-4 w-4 rounded bg-[#a0303f]" />
            <span>Penuh / Libur</span>
          </div>
          <div className="flex items-center gap-2">
            <span className="h-4 w-4 rounded bg-[var(--public-primary-hover)]" />
            <span>Pilihan Anda</span>
          </div>
        </div>
      ) : (
        <div className="mt-5 rounded-2xl border border-dashed border-[var(--public-border)] bg-[var(--public-surface-soft)] px-4 py-3 text-sm text-[var(--public-text-muted)]">
          Pilih ruangan terlebih dahulu agar kalender menampilkan warna
          ketersediaan sesuai ruangan yang dipilih.
        </div>
      )}
    </div>
  );
};

export default BookingCalendar;
