export type VerificationField = {
  label: string;
  value: string;
};

export type DocumentVerificationProps = {
  title: string;
  status: string;
  fields: VerificationField[];
  scannedAt: string;
};
