import { Component, ChangeDetectionStrategy } from '@angular/core';
import { Template, Page } from '../model'
import { Observable } from 'rxjs/Observable';
import { Store } from '@ngrx/store';
import * as fromRoot from '../datastore/Template.ds';
import * as templateact from '../actions/Template.act';
import * as _ from 'lodash'

@Component ({
    selector: "templatecontainer",
    template: `
        <div>page selector here</div>
        <pagecontainer [objPage]="objPage$ | async"></pagecontainer>
    `,
    changeDetection: ChangeDetectionStrategy.OnPush
})

export class TemplateComponent {
    //Template$: Observable<Template>;
    objPage$: Observable<Page>;
    objPage: Page;
    constructor(store: Store<fromRoot.State>) {
        //store.dispatch(new templateact.SetTemplateIdAction({TemplateId: "base"}));
        this.objPage$ = store.select((state) => state.selectedTemplate ? _.find(state.selectedTemplate.Pages, {'CodeName': "home"}) : null);//.subscribe(selectedTemplate => {debugger; return this.objPage = selectedTemplate ? _.find(selectedTemplate.Pages, {'CodeName': "home"}) : null;});
        debugger;
        //this.objPage$ = this.Template$.scan((acc, value) => value ? _.find(value.Pages, {'CodeName': "home"}) : null)
        //this.objPage$ = store.select((state) =>  state.selectedTemplate ? _.find(state.selectedTemplate.Pages, {'CodeName': "home"}) : null);
        //console.log("Template Component");
        //console.log(this.objPage$);
        //this.Template$ = store.select(fromRoot.getTemplate);
    }
    // objTemplate: Template = {
    //                 Id: 1, 
    //                 Name: "Template1", 
    //                 Description: "template1 desc", 
    //                 CodeName:"base", 
    //                 Image: "", 
    //                 DemoUrl:"", 
    //                 Pages:null
    // };
    // objPage: Page = {
    //     Id: 1,
    //     Name: "Home",
    //     Description: "home desc",
    //     CodeName: "home",
    //     Route: "",
    //     Regions: null            
    // };
}