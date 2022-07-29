import { Injectable } from '@angular/core'
import { Section, Collection, Field, FieldVal, Lookup } from '../model'
import { Http, Response, Headers, URLSearchParams } from '@angular/http';
import { BehaviorSubject } from 'rxjs/BehaviorSubject';
import { Observable } from 'rxjs/Observable';
import { environment } from '../environments/environment';
import 'rxjs/add/operator/mergeMap';
import 'rxjs/Rx';
import * as _ from 'lodash'
import { CommonService } from './common.service'



@Injectable()
export class SectionService {
    public Section$ = new BehaviorSubject<Section>(null);
    public Collections$ = new BehaviorSubject<Collection[]>([]);
    public Fields$ = new BehaviorSubject<Field[]>([]);
    public FieldsVal$ = new BehaviorSubject<FieldVal[]>([]);
    public Links$:Array<string>;

    public reqMapData$ = new BehaviorSubject<any>(null);
    private ServiceUrl(method: string): string {
        return 'teditor/' + method;
    }

    constructor(private http: Http, private commonService: CommonService) {

    }

    LoadSection(SectionId: number): Observable<Section> {
        const params = new URLSearchParams();
        params.set("SectionId", SectionId.toString());
        this.commonService.ShowProgressSpinner();
        return this.http.get(this.ServiceUrl("getSectionFields"), { search: params }).map((res) => {
            return this.mapObjects(res.json());
        }).do(() => {
            this.commonService.HideProgressSpinner();
            //if (this.commonService.selectedPageCodeName == "")
            this.commonService.PreviewTemplate(SectionId);
        });
        // .subscribe((resJSON) => {
        //     this.mapObjects(resJSON);
        // },
        // error => console.log("An error occurred when requesting getSectionFields.", error));
    }

    LoadLinks(search_word = "") {
        this.commonService.ShowProgressSpinner();
        let params = new URLSearchParams();
        params.append("search", search_word)
        return this.http.get(this.ServiceUrl("getLinks"),{ search : params }).map((res) => {
            console.log(res.json()['Links']);
            return res.json()['Links'];
        }).do(() => {
            this.commonService.HideProgressSpinner();
            //if (this.commonService.selectedPageCodeName == "")
            this.commonService.PreviewTemplate();
        });
        // .subscribe((resJSON) => {
        //     this.mapObjects(resJSON);
        // },
        // error => console.log("An error occurred when requesting getSectionFields.", error));
    }

    mapObjects(res: any) {
        let _Section = typeof res.Section != "undefined" ? res.Section : this.Section$.getValue();
        let _Collections = typeof res.Collections != "undefined" ? res.Collections : this.Collections$.getValue();
        let _Fields = typeof res.Fields != "undefined" ? res.Fields : this.Fields$.getValue();
        let _FieldsVal = typeof res.FieldsVal != "undefined" ? res.FieldsVal : this.FieldsVal$.getValue();

        _Fields.forEach((el, index) => {
            let _values = _.filter<FieldVal>(_FieldsVal, (item) => item.ObjectFieldId == el.id);
            _.extend(_Fields[index], { Values: _values });
        });

        _Collections = _.sortBy<Collection>(_Collections, (c) => parseInt(c.SortOrder.toString()));
        _Collections.forEach((el, index) => {
            let _fields = _.filter<Field>(_Fields, (item) => item.ObjectId == el.id && item.ObjectType == "ECCOLLECTION");//.sort(f => f.SortOrder);
            _fields = _.sortBy<Field>(_fields, (f) => parseInt(f.SortOrder.toString()));
            _.extend(_Collections[index], { Fields: _fields });
        });

        let _sectionFields = _.filter<Field>(_Fields, (item) => item.ObjectId == _Section.id && item.ObjectType == "ECSECTION");//.sort(f => f.SortOrder);
        _sectionFields = _.sortBy<Field>(_sectionFields, (f) => parseInt(f.SortOrder.toString()));
        _.extend(_Section, { Collections: _Collections, Fields: _sectionFields });

        this.FieldsVal$.next(_FieldsVal);
        this.Fields$.next(_Fields);
        this.Collections$.next(_Collections);
        this.Section$.next(_Section);
        if (res.ReqMapData != "undefined") {
            //debugger;
            let _languages1 = [];
            Object.keys(res.ReqMapData.languages).forEach((c) => _languages1.push(res.ReqMapData.languages[c]));
            let _mapData = Object.assign(res.ReqMapData, { languages1: _languages1 })
            this.reqMapData$.next(res.ReqMapData);
        }
        return _Section;

    }

    AddCollection(SectionId: number): Observable<Collection> {
        const body = new URLSearchParams();
        body.set("SectionId", SectionId.toString());

        let headers = new Headers();
        headers.append('Content-Type', 'application/x-www-form-urlencoded');
        this.commonService.ShowProgressSpinner();
        return this.http.post(this.ServiceUrl("AddCollection"), body.toString(), {
            headers: headers
        }).map(res => {
            let jsonres = res.json();
            console.log(jsonres);
            let _collection = jsonres.Collection;
            let _Fields = jsonres.Fields;
            let _FieldsVal = jsonres.FieldsVal;

            _Fields.forEach((el, index) => {
                let _values = _.filter<FieldVal>(_FieldsVal, (item) => item.ObjectFieldId == el.id);
                _.extend(_Fields[index], { Values: _values });
            });

            _.extend(_collection, { Fields: _Fields });

            return _collection;
        }).do(() => {
            this.commonService.HideProgressSpinner();
            this.commonService.PreviewTemplate(this.Section$.getValue().id);
        })
    }

    RemoveCollection(CollectionId: number): Observable<any> {
        const body = new URLSearchParams();
        body.set("CollectionId", CollectionId.toString());

        let headers = new Headers();
        headers.append('Content-Type', 'application/x-www-form-urlencoded');
        this.commonService.ShowProgressSpinner();
        return this.http.post(this.ServiceUrl("RemoveCollection"), body.toString(), {
            headers: headers
        }).map(res => res.json()).do(() => {
            this.commonService.HideProgressSpinner();
            this.commonService.PreviewTemplate(this.Section$.getValue().id);
        });
    }

    ReorderCollections(Orders: Array<{ CollectionId, SortOrder }>) {
        const body = new URLSearchParams();
        Orders.forEach(order => {
            body.set(order.CollectionId, order.SortOrder);
        })

        let headers = new Headers();
        headers.append('Content-Type', 'application/x-www-form-urlencoded');
        this.commonService.ShowProgressSpinner();
        return this.http.post(this.ServiceUrl("ReorderCollections"), body.toString(), {
            headers: headers
        }).map(res => res.json()).do(() => {
            this.commonService.HideProgressSpinner();
            this.commonService.PreviewTemplate(this.Section$.getValue().id);
        })
    }

    GetLookup(LookupKey: string): Observable<Lookup[]> {
        const params = new URLSearchParams();
        params.set("LookupKey", LookupKey);

        return this.http.get(this.ServiceUrl("getLookup"), { search: params }).map((res) => (<Lookup[]>res.json().Lookup));
    }

    GetObjBrowseItems(FieldType: string) {
        const params = new URLSearchParams();
        params.set("FieldType", FieldType);

        return this.http.get(this.ServiceUrl("getObjBrowseItems"), { search: params }).map((res) => res.json().Items);
    }

    GetImageThumb(Image: string) {
        const params = new URLSearchParams();
        params.set("Image", Image);

        //returns ImageThumb and NoImageThumb
        return this.http.get(this.ServiceUrl("getImageThumb"), { search: params }).map((res) => res.json());
    }

    SearchProducts(ProductName: string) {
        const params = new URLSearchParams();
        params.set("ProductName", ProductName);

        return this.http.get(this.ServiceUrl("searchProducts"), { search: params }).map((res) => res.json().Products);
    }

    SearchCategories(CategoryName: string) {
        const params = new URLSearchParams();
        params.set("CategoryName", CategoryName);

        return this.http.get(this.ServiceUrl("searchCategories"), { search: params }).map((res) => res.json().Categories);
    }

    SearchBrands(BrandName: string) {
        const params = new URLSearchParams();
        params.set("BrandName", BrandName);

        return this.http.get(this.ServiceUrl("searchBrands"), { search: params }).map((res) => res.json().Brands);
    }

    SaveFieldsValChanges(changes: FieldVal[]) {
        const body = new URLSearchParams();
        changes.forEach(f => {
            body.set(f.id.toString(), f.Value);
        })

        let headers = new Headers();
        headers.append('Content-Type', 'application/x-www-form-urlencoded');
        this.commonService.ShowProgressSpinner();
        return this.http.post(this.ServiceUrl("SaveFieldsVal"), body.toString(), {
            headers: headers
        }).map(res => res.json())
            .do(() => {
                this.commonService.HideProgressSpinner();
                this.commonService.PreviewTemplate(this.Section$.getValue().id);
            })
    }

}