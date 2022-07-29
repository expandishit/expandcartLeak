import { NgModule }              from '@angular/core';
import { RouterModule, Routes }  from '@angular/router';
import { TemplateComponent } from './containers/template.component'
import { PageComponent } from './containers/page.component'
import { RegionComponent } from './containers/region.component'
//import { PageNotFoundComponent } from './not-found.component';

const appRoutes: Routes = [
  { path: 'template', component: TemplateComponent },
  { path: 'page', component: PageComponent },
  { path: 'region/:id', component: RegionComponent },
  //{ path: 'heroes',        component: HeroListComponent },
  { path: '', component: TemplateComponent },
  //{ path: '',   redirectTo: '/page', pathMatch: 'full' },
  //{ path: '**', component: PageNotFoundComponent }
];
@NgModule({
  imports: [
    RouterModule.forRoot(appRoutes)
  ],
  exports: [
    RouterModule
  ]
})
export class AppRoutingModule {}