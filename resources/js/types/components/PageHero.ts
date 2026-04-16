export type HeroAction = {
  label: string;
  href: string;
  variant?: 'primary' | 'secondary';
};

export type PageHeroProps = {
  title: string;
  description: string;
  actions: HeroAction[];
};
