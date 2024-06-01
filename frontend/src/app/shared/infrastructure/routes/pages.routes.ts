import { Routes } from '@angular/router';
import { DefaultPageComponent } from '@app/shared/ui/pages/default-page/default-page.component';
import { PageNotFoundComponent } from '@app/shared/ui/pages/page-not-found/page-not-found.component';

export const pagesRoutes: Routes = [
  {
    path: '',
    component: DefaultPageComponent
  },
  {
    path: '**',
    component: PageNotFoundComponent
  }
];
