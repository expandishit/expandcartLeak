import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { TemplateComponent } from './containers/template.component'
import { PageComponent } from './containers/page.component'
import { RegionComponent } from './containers/region.component'
import { RegionSectionsComponent } from './containers/regionsections.component'
//import { SpecialRegionComponent } from './containers/specialregion.component'
import { SectionComponent } from './containers/section.component'
//import { PageNotFoundComponent } from './not-found.component';

const appRoutes: Routes = [
  { path: 'template', component: TemplateComponent },
  { path: 'page', component: PageComponent },
  { path: 'region/:id', component: RegionComponent },
  { path: 'regionsections/:id', component: RegionSectionsComponent },
  //{ path: 'specialregion/:codename', component: SpecialRegionComponent },
  { path: 'section/:id', component: SectionComponent },
  { path: 'section/:id/:type', component: SectionComponent },
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
export class AppRoutingModule { }