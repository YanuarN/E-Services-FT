export type DocumentVerificationProps = {
  title: string;
  status: string;
  letterNumber: string;
  letterDate: string;
  subject: string;
  studentName: string;
  documentUrl: string | null;
  scannedAt: string;
  fields?: Array<{
    label: string;
    value: string;
  }>;
};
