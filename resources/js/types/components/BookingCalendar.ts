export type BookingCalendarProps = {
  selectedDate?: Date;
  month: Date;
  onSelectDate: (date: Date | undefined) => void;
  onMonthChange: (month: Date) => void;
};
