import { Injectable } from '@angular/core'
import { Http, Response, Headers, URLSearchParams } from '@angular/http';
import { BehaviorSubject } from 'rxjs/BehaviorSubject';
import { Observable } from 'rxjs/Observable';
import { environment } from '../environments/environment';
import { FieldVal } from '../model'
import { DomSanitizer, SafeResourceUrl } from "@angular/platform-browser";
import 'rxjs/add/operator/mergeMap';
import 'rxjs/Rx';



@Injectable()
export class CommonService {
    public HeaderName$ = new BehaviorSubject<string>("");
    public Languages$ = new BehaviorSubject<any>(null);
    public LanguageObject$ = new BehaviorSubject<any>(null);
    public Res$ = new BehaviorSubject<any>({});
    public selectedPageCodeName = "";
    public selectedPageRoute = "";
    public TemplateHtml$ = new BehaviorSubject<any>(null);
    public PreviousRoute$ = new BehaviorSubject<any>(null);
    public ShowProgressSpinner$ = new BehaviorSubject<boolean>(false);

    //Save Button in top nav bar
    //public showSaveButton$ = new BehaviorSubject<boolean>(false);
    //public saveButtonClicked$ = new BehaviorSubject<boolean>(false);

    private ServiceUrl(method: string): string {
        return 'meditor/meditor/' + method;
    }

    constructor(private http: Http, private sanitizer: DomSanitizer) {

    }

    UpdateObjectName(ObjectType: string, ObjectId: number, NewObjectName: string) {
        const body = new URLSearchParams();
        body.set("ObjectType", ObjectType);
        body.set("ObjectId", ObjectId.toString());
        body.set("NewObjectName", NewObjectName);

        let headers = new Headers();
        headers.append('Content-Type', 'application/x-www-form-urlencoded');

        return this.http.post(this.ServiceUrl("UpdateObjectName"), body.toString(), {
            headers: headers
        }).map(res => res.json());
    }

    Publish() {
        this.ShowProgressSpinner();
        return this.http.get(this.ServiceUrl("Publish")).map((res) => res.json())
        .do(() => {
            this.HideProgressSpinner();
        });
    }

    ResetDraftVersion() {
        this.ShowProgressSpinner();
        return this.http.get(this.ServiceUrl("ResetDraftVersion")).map((res) => res.json())
        .do(() => {
            this.HideProgressSpinner();
        });
    }

    GetLanguages() {
        return this.http.get(this.ServiceUrl("GetLanguages")).map((res) => {
            let Languages = res.json().Languages;
            let _languages = [];
            Object.keys(Languages).forEach((c) => {
                Languages[c].image = "view/image/flags/" + Languages[c].image;
                _languages.push(Languages[c]);
            });
            console.log(_languages);
            this.Languages$.next(_languages);
            this.LanguageObject$.next(_languages[0]);
            return _languages;
        });
    }

    GetResources() {
        return this.http.get("view/template/meditor/assets/resources/resources." + environment.langCode + ".json")
            .map((res: any) => { this.Res$.next(res.json()); console.log(res.json()); });
    }

    ShowProgressSpinner() {
        if(!this.ShowProgressSpinner$.getValue())
            this.ShowProgressSpinner$.next(true);
    }

    HideProgressSpinner() {
        if(this.ShowProgressSpinner$.getValue())
            this.ShowProgressSpinner$.next(false);
    }

}