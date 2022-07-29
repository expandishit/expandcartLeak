import { NgModule }      from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { AppRoutingModule } from './app.router';
//import { MATERIAL_COMPATIBILITY_MODE } from '@angular/material'

import { AppComponent }  from './app.component';
import {LocationStrategy, HashLocationStrategy} from '@angular/common';
//import {PlatformLocation, MyPlatformLocation} from '@angular/common';
//import {Location, LocationStrategy, PathLocationStrategy} from '@angular/common';

//containers
import { TemplateComponent } from './containers/template.component'
import { PageComponent } from './containers/page.component'
import { RegionComponent } from './containers/region.component'
import { SectionComponent } from './containers/section.component'
import { RegionSectionsComponent } from './containers/regionsections.component'
import { SpecialRegionComponent } from './containers/specialregion.component'

//controls
import {GridlayoutComponent} from './components/gridlayout/gridlayout.component'
import {GlRegionSectionsComponent} from './components/gridlayout/glregionsections.component'
import {ControlRendererComponent} from './components/control.renderer'
import * as controls from "./components/controls/";
//import {TextBoxControl} from './components/controls/textbox.control'
//import { AccordionModule } from 'ng2-bootstrap/accordion';
import {
  MatAutocompleteModule,
  MatButtonModule,
  MatButtonToggleModule,
  MatCardModule,
  MatCheckboxModule,
  MatChipsModule,
  MatDatepickerModule,
  MatDialogModule,
  MatExpansionModule,
  MatGridListModule,
  MatIconModule,
  MatInputModule,
  MatListModule,
  MatMenuModule,
  MatNativeDateModule,
  MatPaginatorModule,
  MatProgressBarModule,
  MatProgressSpinnerModule,
  MatRadioModule,
  MatRippleModule,
  MatSelectModule,
  MatSidenavModule,
  MatSliderModule,
  MatSlideToggleModule,
  MatSnackBarModule,
  MatSortModule,
  MatTableModule,
  MatTabsModule,
  MatToolbarModule,
  MatTooltipModule,
  MatStepperModule,
} from '@angular/material';
//import { MdIconModule, MdIconRegistry } from '@angular/material/icon';
import { Md2ColorpickerModule, Md2SelectModule, MATERIAL_COMPATIBILITY_MODE }  from 'md2'; //TODO: this should be migrated to official material in future when all components are ready
import { TagInputModule } from 'ngx-chips';
import 'hammerjs';
import { DragulaModule } from 'ng2-dragula/ng2-dragula'
//import {ControlRendererComponent} from './components/common/control.renderer'
//import {DynamicFormComponent} from './components/common/dynamic-form'

import { TemplateService } from './services/template.service'
import { CommonService } from './services/common.service'
import { HttpModule } from '@angular/http';
import { ReactiveFormsModule, FormsModule } from '@angular/forms'

@NgModule({
  imports: [ 
    BrowserModule,
    BrowserAnimationsModule,
    AppRoutingModule ,
    HttpModule,
    ReactiveFormsModule,
    FormsModule,

    DragulaModule,

    //MaterialModule.,
    Md2ColorpickerModule, 
    Md2SelectModule,
    TagInputModule,
    //bootstrap modules
    //AccordionModule.forRoot()
    
    //CdkTableModule,
    MatAutocompleteModule,
    MatButtonModule,
    MatButtonToggleModule,
    MatCardModule,
    MatCheckboxModule,
    MatChipsModule,
    MatStepperModule,
    MatDatepickerModule,
    MatDialogModule,
    MatExpansionModule,
    MatGridListModule,
    MatIconModule,
    MatInputModule,
    MatListModule,
    MatMenuModule,
    MatNativeDateModule,
    MatPaginatorModule,
    MatProgressBarModule,
    MatProgressSpinnerModule,
    MatRadioModule,
    MatRippleModule,
    MatSelectModule,
    MatSidenavModule,
    MatSliderModule,
    MatSlideToggleModule,
    MatSnackBarModule,
    MatSortModule,
    MatTableModule,
    MatTabsModule,
    MatToolbarModule,
    MatTooltipModule,
  ],
  declarations: [ 
    AppComponent,

    //containers
    TemplateComponent,
    PageComponent,
    RegionComponent,
    SectionComponent,
    RegionSectionsComponent,
    SpecialRegionComponent,

    //controls
    GridlayoutComponent,
    GlRegionSectionsComponent,
    ControlRendererComponent,
    controls.TextBoxControl,
    controls.TextAreaControl,
    controls.ImageControl,
    controls.EditableLabelControl
    //DynamicFormComponent
  ],
  providers: [
    TemplateService,
    CommonService,
    //MdIconRegistry,
    //OVERLAY_PROVIDERS,
    //{provide: LocationStrategy, useClass: PathLocationStrategy}
    //{provide:PlatformLocation, useClass: MyPlatformLocation}
    {provide: MATERIAL_COMPATIBILITY_MODE, useValue: true},
    {provide: LocationStrategy, useClass: HashLocationStrategy}
  ],
  bootstrap: [ 
    AppComponent
  ]

})
export class AppModule { }
