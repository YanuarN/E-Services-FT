import {
  eachDayOfInterval,
  format,
  isSameDay,
  parseISO,
  startOfDay,
} from 'date-fns';

import type {
  BookingCalendarEvent,
  BookingDayAvailability,
} from '@/types/Booking';

const PriorityMap = {
  available: 0,
  partial: 1,
  full: 2,
} as const;

const StatusMap = {
  APPROVED: 'full',
  PENDING: 'partial',
  REJECTED: 'available',
} as const;

const normalizeRoomName = (roomName: string) => roomName.trim().toLowerCase();

export const buildAvailabilityMap = (
  events: BookingCalendarEvent[],
): Record<string, BookingDayAvailability> => {
  const availabilityMap: Record<string, BookingDayAvailability> = {};

  events.forEach((event) => {
    if (event.status === 'REJECTED') {
      return;
    }

    const days = eachDayOfInterval({
      start: startOfDay(parseISO(event.start)),
      end: startOfDay(parseISO(event.end)),
    });

    days.forEach((day) => {
      const isoDate = format(day, 'yyyy-MM-dd');
      const nextStatus = StatusMap[event.status];
      const current = availabilityMap[isoDate];

      if (!current) {
        availabilityMap[isoDate] = {
          date: isoDate,
          count: 1,
          status: nextStatus,
        };

        return;
      }

      availabilityMap[isoDate] = {
        date: isoDate,
        count: current.count + 1,
        status:
          PriorityMap[nextStatus] > PriorityMap[current.status]
            ? nextStatus
            : current.status,
      };
    });
  });

  return availabilityMap;
};

export const getAvailabilityForDate = (
  date: Date,
  availabilityMap: Record<string, BookingDayAvailability>,
): BookingDayAvailability | null => {
  const key = format(date, 'yyyy-MM-dd');

  return availabilityMap[key] ?? null;
};

export const filterEventsForRoom = (
  events: BookingCalendarEvent[],
  roomId?: number | null,
  roomName?: string,
): BookingCalendarEvent[] => {
  const normalizedRoomName = roomName ? normalizeRoomName(roomName) : '';

  if (roomId) {
    return events.filter((event) => {
      if (event.roomId === roomId) {
        return true;
      }

      return normalizedRoomName
        ? normalizeRoomName(event.roomName) === normalizedRoomName
        : false;
    });
  }

  if (normalizedRoomName) {
    return events.filter(
      (event) => normalizeRoomName(event.roomName) === normalizedRoomName,
    );
  }

  return [];
};

export const findEventsForDate = (
  date: Date,
  events: BookingCalendarEvent[],
): BookingCalendarEvent[] =>
  events.filter((event) => {
    const start = parseISO(event.start);
    const end = parseISO(event.end);

    return (
      isSameDay(date, start) ||
      isSameDay(date, end) ||
      (start < date && end > date)
    );
  });
