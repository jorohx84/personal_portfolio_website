import { Component, HostListener } from '@angular/core';

interface Project {
  title: string;
  description: string;
  stack: string[];
  type: string;
  accent: string;
}

@Component({
  selector: 'app-root',
  standalone: true,
  templateUrl: './app.component.html',
  styleUrl: './app.component.scss'
})
export class AppComponent {
  menuOpen = false;
  activeSection = 'home';

  projects: Project[] = [
    {
      title: 'Tanema Workspace',
      description: 'A modular business platform for CRM, projects, activities, capacity planning and reporting.',
      stack: ['Angular', 'Django', 'DRF', 'PostgreSQL'],
      type: 'SaaS / Business Software',
      accent: '01'
    },
    {
      title: 'Tanema Relations',
      description: 'A focused CRM experience for customers, contacts, activities, tasks and sales workflows.',
      stack: ['Angular', 'TypeScript', 'Django'],
      type: 'CRM',
      accent: '02'
    },
    {
      title: 'Tanema Projects',
      description: 'Project execution with planning, forecasting, team capacity and real-time collaboration.',
      stack: ['Angular', 'Django Channels', 'Redis'],
      type: 'Project Management',
      accent: '03'
    }
  ];

  @HostListener('window:scroll')
  onScroll(): void {
    const sections = ['home', 'about', 'skills', 'work', 'experience', 'contact'];
    const y = window.scrollY + 160;
    for (const id of sections) {
      const el = document.getElementById(id);
      if (el && y >= el.offsetTop) this.activeSection = id;
    }
  }

  scrollTo(id: string): void {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
    this.menuOpen = false;
  }
}
