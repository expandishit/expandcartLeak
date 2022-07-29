import { Injectable } from '@angular/core';
import { Location } from '@angular/common';
import { Template, Page, Region, Section, Collection, Field, FieldVal, Layout } from '../model';
import { Http, Response, Headers, URLSearchParams } from '@angular/http';
import { BehaviorSubject } from 'rxjs/BehaviorSubject';
import { Observable } from 'rxjs/Observable';
import { environment } from '../environments/environment';
import { CommonService } from './common.service';
import 'rxjs/add/operator/mergeMap';
import 'rxjs/Rx';
import * as _ from 'lodash';



@Injectable()
export class TemplateService {

    private ServiceUrl(method: string): string {
        return 'meditor/meditor/' + method;
    }

    public isLoaded$ = new BehaviorSubject<boolean>(false);
    public objTemplate$ = new BehaviorSubject<Template>(null);
    public Pages$ = new BehaviorSubject<Page[]>([]);
    public Regions$ = new BehaviorSubject<Region[]>([]);
    public UserSections$ = new BehaviorSubject<Section[]>([]);
    //public HeaderRegion$ = new BehaviorSubject<Region>(null);
    //public FooterRegion$ = new BehaviorSubject<Region>(null);
    public SettingsPage$ = new BehaviorSubject<Page>(null);
    public Layouts$ = new BehaviorSubject<Layout>(null);

    constructor(private http: Http, private location: Location, public commonService: CommonService) {
    }
    LoadTemplate(): Observable<Template> {
        this.commonService.ShowProgressSpinner();
        return this.http.get(this.ServiceUrl("getTemplateStructure")).map((res) => this.mapObjects(res.json()))
        .do(() => {
            this.commonService.HideProgressSpinner();
        });
        // .subscribe((resJSON) => {
        //     this.mapObjects(resJSON);
        // },
        // error => console.log("An error occurred when requesting getTemplateStructure.", error));
    }

    public mapObjects(res: any): Template {
        let _Template = typeof res.Template != "undefined" ? res.Template : this.objTemplate$.getValue();
        let _TemplatePages = typeof res.Pages != "undefined" ? _.filter<Page>(res.Pages, (item) => item.CodeName != "templatesettings") : this.Pages$.getValue();
        let _TemplateRegions = typeof res.Regions != "undefined" ? _.filter<Region>(res.Regions, reg => reg.CodeName != "header" && reg.CodeName != "footer") : this.Regions$.getValue();
        let _TemplateUserSections = typeof res.UserSections != "undefined" ? res.UserSections : this.UserSections$.getValue();
        //let _HeaderRegion = typeof res.Regions != "undefined" ? _.find<Region>(res.Regions, reg => reg.CodeName == "header") : this.HeaderRegion$.getValue();
        //let _FooterRegion = typeof res.Regions != "undefined" ? _.find<Region>(res.Regions, reg => reg.CodeName == "footer") : this.FooterRegion$.getValue();
        let _SettingsPage = typeof res.Pages != "undefined" ? _.find<Page>(res.Pages, page => page.CodeName == "templatesettings") : this.SettingsPage$.getValue();
        let _Layouts = typeof res.Layouts != "undefined" ? res.Layouts : this.Layouts$.getValue();

        _TemplateRegions.forEach((el, index) => {
            let _userSections = _.filter<Section>(_TemplateUserSections, (item) => item.RegionId == el.id);
            _userSections = _.sortBy<Section>(_userSections, (s) => parseInt(s.SortOrder.toString()));
            //console.log(_userSections);
            _.extend(_TemplateRegions[index], { UserSections: _userSections });
        });

        //_.extend(_HeaderRegion, { UserSections: _.filter<Section>(_TemplateUserSections, (item) => item.RegionId == _HeaderRegion.id) });
        //_.extend(_FooterRegion, { UserSections: _.filter<Section>(_TemplateUserSections, (item) => item.RegionId == _FooterRegion.id) });

        _TemplatePages.forEach((el, index) => {
            let _regions = _.filter<Region>(_TemplateRegions, (item) => item.PageId == el.id);
            _.extend(_TemplatePages[index], { Regions: _regions });
        });

        //_.extend(_SettingsPage, { Regions: _.filter<Region>(_TemplateRegions, (item) => item.PageId == _SettingsPage.id) });

        _.extend(_Template, { Pages: _TemplatePages });

        this.UserSections$.next(_TemplateUserSections);
        //this.HeaderRegion$.next(_HeaderRegion);
        //this.FooterRegion$.next(_FooterRegion);
        this.Regions$.next(_TemplateRegions);
        this.Pages$.next(_TemplatePages);
        this.objTemplate$.next(_Template);
        this.SettingsPage$.next(_SettingsPage);
        this.Layouts$.next(_Layouts);
        this.isLoaded$.next(true);
        return _Template;

    }

    insertSection(sourceSectionId: number, sourceType: string, destType: string, destSortOrder: number) {

        const body = new URLSearchParams();

        // Object.keys(value).forEach(key => {
        //     body.set(key, value[key]);
        // }

        body.set("sourceSectionId", sourceSectionId.toString());
        body.set("sourceType", sourceType.toString());
        body.set("destType", destType.toString());
        body.set("destSortOrder", destSortOrder.toString());
        //const body = "SectionId=" + SectionId;

        let headers = new Headers();
        headers.append('Content-Type', 'application/x-www-form-urlencoded');
        this.commonService.ShowProgressSpinner();
        return this.http.post(this.ServiceUrl("insertSection"), body.toString(), {
            headers: headers
        }).map(res => res.json())
            .do(() => this.LoadTemplate().subscribe())
            .do(() => {
                this.commonService.HideProgressSpinner();
            })
        // .subscribe((resJSON) => {
        //     this.mapObjects(resJSON);
        // },
        // error => console.log("An error occurred when requesting insertSection.", error));
    }

    getRegionAvailableSections(RegionId: number): Observable<Section[]> {
        const body = new URLSearchParams();
        body.set("RegionId", RegionId.toString());

        let headers = new Headers();
        headers.append('Content-Type', 'application/x-www-form-urlencoded');
        return this.http.post(this.ServiceUrl("getRegionAvailableSections"), body.toString(), {
            headers: headers
        }).map(res => res.json().AvailableSections)
    }

    ReorderSections(Orders: Array<{ SectionId, SortOrder }>) {
        const body = new URLSearchParams();
        Orders.forEach(order => {
            body.set(order.SectionId, order.SortOrder);
        })

        let headers = new Headers();
        headers.append('Content-Type', 'application/x-www-form-urlencoded');

        return this.http.post(this.ServiceUrl("ReorderSections"), body.toString(), {
            headers: headers
        }).map(res => res.json());
    }

    RemoveSection(SectionId: number): Observable<any> {
        const body = new URLSearchParams();
        body.set("SectionId", SectionId.toString());

        let headers = new Headers();
        headers.append('Content-Type', 'application/x-www-form-urlencoded');
        this.commonService.ShowProgressSpinner();
        return this.http.post(this.ServiceUrl("RemoveSection"), body.toString(), {
            headers: headers
        }).map(res => {
            let resJSON = res.json();
            this.mapObjects(resJSON);
            return resJSON.status;
        }).do(() => {
            this.commonService.HideProgressSpinner();
        });
    }

    UpdateSectionState(SectionId: number, State: string) {
        const body = new URLSearchParams();
        body.set("SectionId", SectionId.toString());
        body.set("State", State.toString());

        let headers = new Headers();
        headers.append('Content-Type', 'application/x-www-form-urlencoded');
        this.commonService.ShowProgressSpinner();
        return this.http.post(this.ServiceUrl("UpdateSectionState"), body.toString(), {
            headers: headers
        }).map(res => res.json()).do(() => {
            this.commonService.HideProgressSpinner();
        });
    }

    AddLayout(Route: string, Name: string): Observable<any> {
        const body = new URLSearchParams();
        body.set("Route", Route);
        body.set("Name", Name);

        let headers = new Headers();
        headers.append('Content-Type', 'application/x-www-form-urlencoded');
        this.commonService.ShowProgressSpinner();
        return this.http.post(this.ServiceUrl("AddLayout"), body.toString(), {
            headers: headers
        }).map(res => {
            let resJSON = res.json();
            return resJSON.status;
        })
        //.do(() => this.LoadTemplate().subscribe())
        .do(() => {
            this.commonService.HideProgressSpinner();
        });
    }

}