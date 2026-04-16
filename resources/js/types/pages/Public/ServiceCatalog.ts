export type ServiceField = {
  name: string;
  label: string;
  type: string;
  required: boolean;
  placeholder?: string;
  helpText?: string;
  rows?: number;
  step?: string;
};

export type PublicLetterService = {
  key: string;
  title: string;
  description: string;
  fields: ServiceField[];
};

export type ServicesProps = {
  services: PublicLetterService[];
};

export type ServiceFormProps = {
  service: PublicLetterService;
  services: PublicLetterService[];
  studyPrograms: string[];
};

export type SharedPageProps = {
  flash?: {
    success?: string;
  };
};

export type MemberFormRow = {
  id: string;
  name: string;
  nim: string;
  studyProgram: string;
};
