import { Link, useForm, usePage } from '@inertiajs/react';
import { FormEvent, useEffect, useMemo, useState } from 'react';

import AppLayout from '@/components/Layout/AppLayout/AppLayout';
import type {
  MemberFormRow,
  ServiceField,
  ServiceFormProps,
  SharedPageProps,
} from '@/types/pages/Public/ServiceCatalog';

const MEMBER_FIELD_NAMES = ['student_list', 'group_member'];

const isMemberField = (field: ServiceField): boolean =>
  field.type === 'textarea' && MEMBER_FIELD_NAMES.includes(field.name);

const createMemberRow = (): MemberFormRow => ({
  id: `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
  name: '',
  nim: '',
  studyProgram: '',
  phoneNumber: '',
});

const serializeMemberRows = (rows: MemberFormRow[]): string =>
  rows
    .filter((row) =>
      [row.name, row.nim, row.studyProgram, row.phoneNumber].some((value) =>
        Boolean(value.trim()),
      ),
    )
    .map((row) =>
      [
        row.name.trim(),
        row.nim.trim(),
        row.studyProgram.trim(),
        row.phoneNumber.trim(),
      ].join(' - '),
    )
    .filter(Boolean)
    .join('\n');

const buildRowsFromValue = (value: string): MemberFormRow[] => {
  const rows = value
    .split(/\r\n|\n|\r/)
    .map((line) => line.trim())
    .filter(Boolean)
    .map((line) => {
      const [name = '', nim = '', studyProgram = '', phoneNumber = ''] = line
        .split('-')
        .map((part) => part.trim());

      return {
        id: `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
        name,
        nim,
        studyProgram,
        phoneNumber,
      };
    });

  return rows.length > 0 ? rows : [createMemberRow()];
};

const ServiceForm = ({
  service,
  services,
  studyPrograms,
}: ServiceFormProps) => {
  const { flash } = usePage<SharedPageProps>().props;

  const initialData = service.fields.reduce<Record<string, string>>(
    (acc, field) => {
      acc[field.name] = '';
      return acc;
    },
    {},
  );

  const { data, setData, post, processing, errors } = useForm(initialData);

  useEffect(() => {
    if (flash?.whatsappUrl) {
      window.location.assign(flash.whatsappUrl);
    }
  }, [flash?.whatsappUrl]);

  const memberFields = useMemo(
    () => service.fields.filter((field) => isMemberField(field)),
    [service.fields],
  );

  const [memberRowsByField, setMemberRowsByField] = useState<
    Record<string, MemberFormRow[]>
  >(() =>
    memberFields.reduce<Record<string, MemberFormRow[]>>((acc, field) => {
      acc[field.name] = buildRowsFromValue(initialData[field.name] ?? '');
      return acc;
    }, {}),
  );

  const updateMemberRows = (fieldName: string, rows: MemberFormRow[]) => {
    setMemberRowsByField((prev) => ({
      ...prev,
      [fieldName]: rows,
    }));

    setData(fieldName, serializeMemberRows(rows));
  };

  const handleAddMember = (fieldName: string) => {
    const currentRows = memberRowsByField[fieldName] ?? [createMemberRow()];
    updateMemberRows(fieldName, [...currentRows, createMemberRow()]);
  };

  const handleRemoveMember = (fieldName: string, rowId: string) => {
    const currentRows = memberRowsByField[fieldName] ?? [];
    const nextRows = currentRows.filter((row) => row.id !== rowId);

    updateMemberRows(
      fieldName,
      nextRows.length > 0 ? nextRows : [createMemberRow()],
    );
  };

  const handleMemberChange = (
    fieldName: string,
    rowId: string,
    key: keyof Omit<MemberFormRow, 'id'>,
    value: string,
  ) => {
    const currentRows = memberRowsByField[fieldName] ?? [createMemberRow()];
    const nextRows = currentRows.map((row) =>
      row.id === rowId
        ? {
            ...row,
            [key]: value,
          }
        : row,
    );

    updateMemberRows(fieldName, nextRows);
  };

  const handleSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    post(`/form/${service.key}`);
  };

  const renderMemberField = (field: ServiceField, commonClassName: string) => {
    const rows = memberRowsByField[field.name] ?? [createMemberRow()];

    return (
      <div className="space-y-4">
        <div className="space-y-3">
          {rows.map((row, index) => (
            <div
              key={row.id}
              className="rounded-lg border border-gray-200 bg-white p-4 shadow-sm"
            >
              <div className="mb-4 flex items-center justify-between">
                <p className="text-xs font-semibold uppercase tracking-wider text-gray-500">
                  Anggota {index + 1}
                </p>
                <button
                  type="button"
                  onClick={() => handleRemoveMember(field.name, row.id)}
                  className="text-xs font-semibold text-red-600 transition hover:text-red-700"
                >
                  Hapus
                </button>
              </div>

              <div className="grid gap-3 md:grid-cols-4">
                <input
                  type="text"
                  value={row.name}
                  required={field.required && index === 0}
                  placeholder="Nama anggota"
                  onChange={(event) =>
                    handleMemberChange(
                      field.name,
                      row.id,
                      'name',
                      event.target.value,
                    )
                  }
                  className={commonClassName}
                />
                <input
                  type="text"
                  value={row.nim}
                  required={field.required && index === 0}
                  placeholder="NIM anggota"
                  onChange={(event) =>
                    handleMemberChange(
                      field.name,
                      row.id,
                      'nim',
                      event.target.value,
                    )
                  }
                  className={commonClassName}
                />
                <select
                  value={row.studyProgram}
                  onChange={(event) =>
                    handleMemberChange(
                      field.name,
                      row.id,
                      'studyProgram',
                      event.target.value,
                    )
                  }
                  className={commonClassName}
                >
                  <option value="">Pilih Prodi</option>
                  {studyPrograms.map((program) => (
                    <option key={`${row.id}-${program}`} value={program}>
                      {program}
                    </option>
                  ))}
                </select>
                <input
                  type="tel"
                  value={row.phoneNumber}
                  placeholder="Nomor HP anggota"
                  onChange={(event) =>
                    handleMemberChange(
                      field.name,
                      row.id,
                      'phoneNumber',
                      event.target.value,
                    )
                  }
                  className={commonClassName}
                />
              </div>
            </div>
          ))}
        </div>

        <button
          type="button"
          onClick={() => handleAddMember(field.name)}
          className="inline-flex items-center rounded-md border border-primary/30 px-4 py-2 text-sm font-semibold text-primary transition hover:border-primary hover:bg-primary hover:text-white"
        >
          Tambah Anggota
        </button>
      </div>
    );
  };

  const renderField = (field: ServiceField) => {
    const commonClassName =
      'w-full rounded-t-md border-0 border-b-2 border-transparent bg-[#F3F4F6] px-4 py-3 text-sm text-primary outline-none transition-colors focus:border-[#F5A623] focus:ring-0';

    if (isMemberField(field)) {
      return renderMemberField(field, commonClassName);
    }

    if (field.type === 'textarea') {
      return (
        <textarea
          rows={field.rows ?? 4}
          required={field.required}
          placeholder={field.placeholder}
          value={data[field.name] ?? ''}
          onChange={(event) => setData(field.name, event.target.value)}
          className={commonClassName}
        />
      );
    }

    if (field.type === 'select_study_program') {
      return (
        <select
          required={field.required}
          value={data[field.name] ?? ''}
          onChange={(event) => setData(field.name, event.target.value)}
          className={commonClassName}
        >
          <option value="">Pilih Program Studi</option>
          {studyPrograms.map((program) => (
            <option key={program} value={program}>
              {program}
            </option>
          ))}
        </select>
      );
    }

    return (
      <input
        type={field.type}
        step={field.step}
        required={field.required}
        placeholder={field.placeholder}
        value={data[field.name] ?? ''}
        onChange={(event) => setData(field.name, event.target.value)}
        className={commonClassName}
      />
    );
  };

  const renderFieldBlock = (field: ServiceField) => (
    <div key={field.name}>
      <label className="mb-2 block text-[0.6875rem] font-medium uppercase tracking-wider text-gray-700">
        {field.label}
        {field.required ? ' *' : ''}
      </label>
      {renderField(field)}
      {field.helpText ? (
        <p className="mt-2 text-xs text-gray-500">{field.helpText}</p>
      ) : null}
      {errors[field.name] ? (
        <p className="mt-2 text-xs text-red-600">{errors[field.name]}</p>
      ) : null}
    </div>
  );

  return (
    <AppLayout currentPath="/services" pageTitle="Form Pengajuan Surat">
      <div className="border-b border-gray-200 bg-white pb-4 pt-8">
        <div className="mx-auto max-w-7xl px-6 md:px-12">
          <div className="mb-6 flex items-center space-x-2 text-sm text-gray-500">
            <Link href="/" className="hover:text-primary">
              Beranda
            </Link>
            <span>/</span>
            <Link href="/services" className="hover:text-primary">
              Layanan
            </Link>
            <span>/</span>
            <span className="max-w-[280px] truncate font-medium text-primary md:max-w-none">
              {service.title}
            </span>
          </div>

          <h1 className="mb-4 text-[2rem] font-bold text-primary">
            Formulir Pengajuan Surat
          </h1>
          <div className="h-[4px] w-[40px] bg-[#F5A623]" />
        </div>
      </div>

      <div className="mx-auto max-w-7xl px-6 py-12 md:px-12">
        <div className="flex flex-col gap-8 lg:flex-row">
          <div className="lg:w-2/3">
            <div className="rounded-xl border border-gray-100 bg-white p-8 shadow-[0_8px_30px_rgba(27,42,107,0.04)]">
              {flash?.success ? (
                <div className="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                  <p>{flash.success}</p>
                  {flash.whatsappUrl ? (
                    <a
                      href={flash.whatsappUrl}
                      target="_blank"
                      rel="noreferrer"
                      className="mt-3 inline-flex items-center rounded-md bg-green-600 px-4 py-2 font-semibold text-white transition hover:bg-green-700"
                    >
                      Lanjutkan ke WhatsApp Admin
                    </a>
                  ) : null}
                </div>
              ) : null}

              <h3 className="mb-2 text-[1.125rem] font-semibold text-primary">
                {service.title}
              </h3>
              <p className="mb-8 text-sm text-gray-500">
                {service.description}
              </p>

              <form className="space-y-6" onSubmit={handleSubmit}>
                {service.fields.map((field) => renderFieldBlock(field))}

                <div className="pt-2">
                  <button
                    type="submit"
                    disabled={processing}
                    className="flex w-full items-center justify-center rounded-[6px] bg-[#F5A623] px-4 py-4 font-bold text-[#1B2A6B] shadow-md transition-all hover:bg-yellow-500 hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-70"
                  >
                    {processing ? 'Mengirim...' : 'Kirim Pengajuan'}
                  </button>
                </div>
              </form>
            </div>
          </div>

          <div className="lg:w-1/3">
            <div className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
              <h4 className="mb-4 border-b border-gray-100 pb-4 font-bold text-primary">
                Layanan Surat Lain
              </h4>
              <div className="space-y-3">
                {services.map((item) => (
                  <Link
                    key={item.key}
                    href={`/form/${item.key}`}
                    className={`block rounded-lg border px-4 py-3 text-sm transition ${
                      item.key === service.key
                        ? 'border-primary bg-blue-50 text-primary'
                        : 'border-gray-200 text-gray-600 hover:border-primary hover:text-primary'
                    }`}
                  >
                    {item.title}
                  </Link>
                ))}
              </div>
              <div className="mt-6 rounded-lg border border-gray-100 bg-[#F8F9FB] p-4 text-sm text-gray-600">
                Notifikasi status pengajuan dikirim melalui WhatsApp ke nomor
                yang Anda isi pada formulir.
              </div>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
};

export default ServiceForm;
