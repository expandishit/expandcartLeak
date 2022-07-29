import { NgModule }      from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { AppRoutingModule } from './app.router';

import { AppComponent }  from './app.component';

//containers
import { TemplateComponent } from './containers/template.component'
import { PageComponent } from './containers/page.component'
import { RegionComponent } from './containers/region.component'

//controls
import {GridlayoutComponent} from './components/gridlayout/gridlayout.component'
import {GlRegionSectionsComponent} from './components/gridlayout/glregionsections.component'

import { StoreModule } from '@ngrx/store';
import { EffectsModule } from '@ngrx/effects';
import { reducer } from './datastore/template.ds'
import { TemplateEffects } from './effects/template.ef';
import { TemplateService } from './services/template.service'
import { HttpModule } from '@angular/http';
//import { RouterStoreModule } from '@ngrx/router-store';
//import { DBModule } from '@ngrx/db';
//import { StoreDevtoolsModule } from '@ngrx/store-devtools';
//import { MaterialModule } from '@angular/material';

@NgModule({
  imports: [ 
    BrowserModule,
    AppRoutingModule ,
    HttpModule,

    StoreModule.provideStore(reducer ),
    EffectsModule.run(TemplateEffects),
    //RouterStoreModule.connectRouter(),

  ],
  declarations: [ 
    AppComponent,

    //containers
    TemplateComponent,
    PageComponent,
    RegionComponent,

    //controls
    GridlayoutComponent,
    GlRegionSectionsComponent, 

    //datastore

  ],
  providers: [
    TemplateService,
  ],
  bootstrap: [ 
    AppComponent
  ]

})
export class AppModule { }
