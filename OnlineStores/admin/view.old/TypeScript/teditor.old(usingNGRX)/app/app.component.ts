import { Component } from '@angular/core';
import { Store } from '@ngrx/store';
import { Observable } from 'rxjs/Observable';
import * as fromRoot from './datastore/Template.ds';
import * as templateact from './actions/Template.act';

@Component({
  selector: 'my-app',
  template: `
      <h1>Header of the App</h1>
      <div class="wrapper" style="width:33%">
        <router-outlet></router-outlet>
      </div>
      <h2>Footer of the App</h2>
      `,
})
export class AppComponent  { 
  name = 'Angular'; 

  constructor(store: Store<fromRoot.State>) {
     store.subscribe( state => console.log('Initial App State: ', state));

    //  store.combineLatest (
    //    SetTemplateIdAction({TemplateId: "base"}),
    //  );
     store.dispatch(new templateact.SetTemplateIdAction({TemplateId: "base"}));
     //store.dispatch(new templateact.SelectPageAction({selectedPageCodeName: "home"}));
  }
}
