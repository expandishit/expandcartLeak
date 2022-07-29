import { Component, Input, Output, EventEmitter, OnInit } from '@angular/core';

@Component({
  selector: 'ec-editablelabel',
  template: `
    <!--<label *ngIf="!isEditMode">{{labelValue}}</label>-->
    <span *ngIf="!isEditMode">{{labelValue}}</span>
    <mat-icon *ngIf="!isEditMode && enableEditing" style="cursor: pointer;font-size: 12px;" (click)="$event.stopPropagation();isEditMode=true;">mode_edit</mat-icon>

    <input *ngIf="isEditMode && enableEditing" type="text" (click)="$event.stopPropagation();" [(ngModel)]="inputValue">
    <mat-icon *ngIf="isEditMode && enableEditing" style="cursor: pointer;font-size: 12px;" (click)="$event.stopPropagation();ApplyNewValue();">check</mat-icon>
    <mat-icon *ngIf="isEditMode && enableEditing" style="cursor: pointer;font-size: 12px;" (click)="$event.stopPropagation();DiscardNewValue();">close</mat-icon>
    <!--<button *ngIf="isEditMode" mat-button (click)="$event.stopPropagation();ApplyNewValue();"><mat-icon>check</mat-icon></button>
    <button *ngIf="isEditMode && enableEditing" mat-button (click)="$event.stopPropagation();DiscardNewValue();"><mat-icon>close</mat-icon></button>-->
  `
})
export class EditableLabelControl implements OnInit {
  @Input() label: string;
  @Input() enableEditing: boolean = false;
  @Output('update') change = new EventEmitter<string>();

  isEditMode: boolean = false;
  labelValue: string;
  inputValue: string;

  ngOnInit() {
      this.labelValue = this.label;
      this.inputValue = this.label;
  }

  ApplyNewValue() {
    this.labelValue = this.inputValue;
    this.isEditMode = false;
    this.change.emit(this.labelValue);
  }
  DiscardNewValue() {
    this.inputValue = this.labelValue;
    this.isEditMode = false;
  }
}