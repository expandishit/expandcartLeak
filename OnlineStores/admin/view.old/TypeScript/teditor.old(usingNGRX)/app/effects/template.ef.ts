import 'rxjs/add/operator/catch';
import 'rxjs/add/operator/map';
import 'rxjs/add/operator/switchMap';
import 'rxjs/add/operator/debounceTime';
import 'rxjs/add/operator/skip';
import 'rxjs/add/operator/takeUntil';
import { Injectable } from '@angular/core';
import { Effect, Actions } from '@ngrx/effects';
import { Action } from '@ngrx/store';
import { Observable } from 'rxjs/Observable';
import { empty } from 'rxjs/observable/empty';
import { of } from 'rxjs/observable/of';

import { TemplateService } from '../services/template.service';
import * as templateact from '../actions/template.act';


@Injectable()
export class TemplateEffects {
  constructor(private actions$: Actions, private templateSrv: TemplateService) { }


    // @Effect()
    // setTemplateId$: Observable<Action> = this.actions$
    //   .ofType(templateact.ActionTypes.SET_TEMPLATEID)
    //   .startWith((action: templateact.SetTemplateIdAction) => action.payload)
    // .switchMap(() =>
    //   this.db.query('books')
    //     .toArray()
    //     .map((books: Book[]) => new collection.LoadSuccessAction(books))
    //     .catch(error => of(new collection.LoadFailAction(error)))
    // );
    @Effect()
    setTemplateId$: Observable<Action> = this.actions$
      .ofType(templateact.ActionTypes.SET_TEMPLATEID)
        .map((action: templateact.SetTemplateIdAction) => action.payload)
        .switchMap(payload => {debugger; return this.templateSrv.getTemplate(payload.TemplateId);})
        .map(res => {
          debugger;
          console.log("here");
          console.log(res);
            return {
                type: templateact.ActionTypes.LOAD_TEMPLATE,
                payload: {Template: res}
            };
        });

}