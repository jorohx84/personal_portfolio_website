import { AfterViewInit, Component, ElementRef, HostListener, ViewChild } from '@angular/core';

interface Project { title: string; description: string; type: string; number: string; short: string; }
interface Service { title: string; description: string; number: string; icon: string; }
interface Skill { name: string; level: number; }

@Component({
  selector: 'app-root',
  standalone: true,
  templateUrl: './app.component.html',
  styleUrl: './app.component.scss'
})
export class AppComponent implements AfterViewInit {
  @ViewChild('skillsSection') skillsSection?: ElementRef<HTMLElement>;
  skillsAnimated = false;
  menuOpen = false;
  activeSection = 'home';

  services: Service[] = [
    { number: '01', icon: '</>', title: 'Web Application Development', description: 'Modern, responsive business applications built with Angular, TypeScript and a clean component architecture.' },
    { number: '02', icon: '{}', title: 'Backend & API Development', description: 'Reliable APIs and business logic with Python, Django and Django REST Framework.' },
    { number: '03', icon: '◎', title: 'SaaS Product Development', description: 'From product idea to production-ready SaaS — including data models, authentication, billing and deployment.' },
    { number: '04', icon: '↗', title: 'Product Engineering', description: 'Turning complex business processes into simple workflows and interfaces people actually enjoy using.' },
    { number: '05', icon: 'DB', title: 'Data & Infrastructure', description: 'PostgreSQL, Redis, Docker and Linux infrastructure for maintainable and scalable applications.' },
    { number: '06', icon: '↯', title: 'Real-time Applications', description: 'Interactive experiences with WebSockets, Django Channels and event-driven application flows.' }
  ];

  skills: Skill[] = [
    { name: 'Angular / TypeScript', level: 95 },
    { name: 'Python / Django', level: 92 },
    { name: 'HTML / SCSS / CSS', level: 94 },
    { name: 'PostgreSQL / Data', level: 88 },
    { name: 'Docker / Linux / Git', level: 82 },
    { name: 'REST APIs / WebSockets', level: 90 }
  ];

  experience = [
    { date: 'NOW', title: 'Fullstack Software Developer', company: 'Tanema', description: 'Building and evolving Tanema, a SaaS platform for CRM, projects, planning and business workflows.' },
    { date: 'CURRENT', title: 'Sales & Business Development', company: 'Chemical Industry', description: 'Working in B2B sales while continuing to develop software products and deepen my understanding of real business processes.' },
    { date: 'ONGOING', title: 'Independent Software Development', company: 'Tanema Business Software', description: 'Designing, developing and operating fullstack web applications from frontend through backend and infrastructure.' }
  ];

  projects: Project[] = [
    { number: '01', short: 'WORKSPACE', title: 'Tanema Workspace', type: 'SaaS / Business Software', description: 'A modular business platform combining CRM, projects, activities, capacity planning and reporting.' },
    { number: '02', short: 'RELATIONS', title: 'Tanema Relations', type: 'CRM', description: 'A focused CRM for customers, contacts, activities, tasks and sales workflows.' },
    { number: '03', short: 'PROJECTS', title: 'Tanema Projects', type: 'Project Management', description: 'Project execution with planning, forecasting, team capacity and real-time collaboration.' }
  ];

  ngAfterViewInit(): void {
    this.checkSkillsVisibility();
  }

  @HostListener('window:scroll')
  onScroll(): void {
    const sections = ['home', 'about', 'services', 'skills', 'resume', 'portfolio', 'contact'];
    const y = window.scrollY + window.innerHeight * 0.35;
    for (const id of sections) {
      const el = document.getElementById(id);
      if (el && y >= el.offsetTop) this.activeSection = id;
    }

    this.checkSkillsVisibility();
  }

  @HostListener('window:resize')
  onResize(): void {
    this.checkSkillsVisibility();
  }

  private checkSkillsVisibility(): void {
    if (!this.skillsSection) return;

    const rect = this.skillsSection.nativeElement.getBoundingClientRect();
    const triggerLine = window.innerHeight * 0.18;
    const shouldAnimate = rect.top <= triggerLine && rect.bottom > 0;

    this.skillsAnimated = shouldAnimate;
  }

  scrollTo(id: string): void {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    this.menuOpen = false;
  }

  async sendMessage(event: Event): Promise<void> {
    event.preventDefault();

    const form = event.target as HTMLFormElement;
    const data = new FormData(form);
    const name = String(data.get('name') ?? '').trim();
    const email = String(data.get('email') ?? '').trim();
    const subject = String(data.get('subject') ?? '').trim();
    const message = String(data.get('message') ?? '').trim();

    const fields = {
      name: form.querySelector('[name="name"]') as HTMLInputElement,
      email: form.querySelector('[name="email"]') as HTMLInputElement,
      subject: form.querySelector('[name="subject"]') as HTMLInputElement,
      message: form.querySelector('[name="message"]') as HTMLTextAreaElement
    };

    Object.values(fields).forEach(field => field.classList.remove('invalid'));

    const invalidFields: HTMLElement[] = [];

    if (!name) invalidFields.push(fields.name);
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) invalidFields.push(fields.email);
    if (!subject) invalidFields.push(fields.subject);
    if (!message) invalidFields.push(fields.message);

    invalidFields.forEach(field => field.classList.add('invalid'));

    const errorBox = form.querySelector('.form-message.error') as HTMLElement | null;
    const successBox = form.querySelector('.form-message.success') as HTMLElement | null;
    const submitButton = form.querySelector('.form-submit') as HTMLButtonElement | null;

    if (errorBox) errorBox.hidden = invalidFields.length === 0;
    if (successBox) successBox.hidden = true;

    if (invalidFields.length > 0) {
      invalidFields[0].focus();
      return;
    }

    if (submitButton) submitButton.disabled = true;

    try {
      const response = await fetch('/api/contact.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, email, subject, message })
      });

      const result = await response.json();

      if (!response.ok || !result.success) {
        throw new Error(result.message || 'Die Nachricht konnte nicht gesendet werden.');
      }

      form.reset();
      Object.values(fields).forEach(field => field.classList.remove('invalid'));
      if (errorBox) errorBox.hidden = true;
      if (successBox) successBox.hidden = false;
    } catch (error) {
      if (errorBox) {
        errorBox.textContent = error instanceof Error
          ? error.message
          : 'Die Nachricht konnte gerade nicht gesendet werden.';
        errorBox.hidden = false;
      }
    } finally {
      if (submitButton) submitButton.disabled = false;
    }
  }
}