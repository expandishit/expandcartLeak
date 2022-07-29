import { Component, Input, Output, EventEmitter, OnInit } from '@angular/core';
import { Field, FieldVal } from '../../model';
import { CommonService } from '../../services/common.service'
//import { SectionService } from '../../services/section.service'
import * as _ from 'lodash'
@Component({
  selector: 'ec-textarea',
  //providers: [SectionService],
  styles: [`
    mat-input-container {
      display: flex;
      flex: 1;
    }

    textarea {
      width: 100%
    }

    .control-container {
      position: relative;
      display: flex;
      flex-flow: row wrap;
      /*width: inherit;*/
    }

    mat-select {
      padding-top: 24px;
      padding-left: 5px;
    }
  `],
  template: `
      <div class="control-container">
        <mat-input-container>
            <textarea matInput #textareaCtrl cols="100" [(ngModel)]="currentValue" (change)="changeValue(textareaCtrl.value)"></textarea>
        </mat-input-container>
        
        <!--<mat-select *ngIf="field.IsMultiLang==1" [ngModel]="field.Values[0].Lang" (change)="changeLang($event.value)">
          <mat-option *ngFor="let lang of field.Values;let i=index" [value]="lang.Lang">
            {{getLanguageName(lang.Lang)}}
          </mat-option>
        </mat-select>-->
      </div>  
        <!--<mat-button-toggle-group *ngIf="field.IsMultiLang==1" (change)="changeLang($event.value)">
            <mat-button-toggle *ngFor="let lang of field.Values;let i=index" [value]="lang.Lang" [checked]="i==0">
            {{lang.Lang}}
            <img [src]="getLanguageIcon(lang.Lang)" />
            </mat-button-toggle>
        </mat-button-toggle-group>-->
        
  `
})
export class TextAreaControl implements OnInit {
  @Input() field: Field;
  @Input() languages: any;
  @Output('update') change: EventEmitter<FieldVal[]> = new EventEmitter<FieldVal[]>();

  currentValue: string;
  currentLang: string;
  values: FieldVal[];

  constructor(public commonService: CommonService) {

  }

  ngOnInit() {
    if (typeof this.field == "undefined") return;
    this.values = this.field.Values;
    //Lang
    if (this.field.IsMultiLang == 1) {
      this.currentLang = this.field.Values[0].Lang;
      this.currentValue = this.field.Values[0].Value;
    }
    else {
      this.currentValue = this.field.Values[0].Value;
    }
    this.commonService.LanguageObject$.subscribe((l) => this.changeLang(l.code));
  }

  changeLang(lang) {
    this.currentLang = lang;
    this.currentValue = _.find<FieldVal>(this.values, (c) => (c.Lang).toLowerCase() == lang).Value;
  }
  changeValue(value) {
    this.values.forEach((val, i) => {
      if (val.Lang == this.currentLang || this.field.IsMultiLang == 0)
        this.values[i].Value = value;
    });
    this.change.emit(this.values);
  }
  getLanguageIcon(LangCode: string) {
    let url = "../view/image/flags/";
    return url + this.languages[LangCode].image;
  }
  getLanguageName(LangCode: string) {
    return this.languages[LangCode].name;
  }
}
