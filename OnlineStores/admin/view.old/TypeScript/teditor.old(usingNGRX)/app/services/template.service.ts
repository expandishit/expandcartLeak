import { Injectable } from '@angular/core'
import { Template } from '../model'
import { Http, Response } from '@angular/http';
import { Observable } from 'rxjs/Rx';


@Injectable()
export class TemplateService {
    private baseUrl: string = '../app/mockdata/_Template.json';
    private objTemplate: Template;

    constructor(private http:Http){
        //    this.http.get(this.baseUrl).subscribe(response => {
        //         this._regions.next(response.json())
      //});
    }
      getTemplate(TemplateId: string): Observable<Template> {
          return this.http.get(this.baseUrl)
                .map(res => res.json() || []);
      }
}