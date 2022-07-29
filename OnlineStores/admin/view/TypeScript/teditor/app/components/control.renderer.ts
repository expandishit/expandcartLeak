import { Component, Input, Output, EventEmitter, OnInit, ViewChild, ElementRef, AfterViewChecked } from '@angular/core';
import { FormGroup,FormControl,FormBuilder }        from '@angular/forms';
import { Field, FieldVal,Link }     from '../model';
import { SectionService } from '../services/section.service'
import {Observable} from 'rxjs'
import {debounceTime} from 'rxjs/operators'
import * as _ from 'lodash'
declare var $:any;

@Component({
  selector: 'control-renderer',
  providers: [SectionService],
  templateUrl: './control.renderer.html',
  styleUrls: ['./control.renderer.css']
})
export class ControlRendererComponent implements OnInit, AfterViewChecked {
  @Input() field:Field;
  @Input() controlOptions:any;
  @Input() languages:any;
  
  searchTerm : FormControl = new FormControl();

  searchResult: Observable<Link[]>;

  @Output('update') change:EventEmitter<FieldVal[]> = new EventEmitter<FieldVal[]>();

  currentLang:string;
  
  //_controlOptions: any;
//@ViewChild('control') control;
  get controlValue():any {
    // if(this.field.Type=="text" && this.field.IsMultiLang==1 && typeof this.currentLang!="undefined") {
    //   console.log(this.currentLang);
    //   return _.find<FieldVal>(this.field.Values, (c) => c.Lang==this.currentLang).Value;
    // }
    if (this.field.Type == "checkbox") {
      return this.field.Values[0].Value == "1";
    }
    else if (this.field.Type.startsWith('sselect-')) {
      let _selectedValue:any;
      if (typeof this.controlOptions.items != "undefined") {
        _selectedValue = this.field.Values[0].Value;
        Object.assign(this.controlOptions, {selectedValue: _selectedValue});
      }
      return this.controlOptions.selectedValue;
    }
    else if (this.field.Type.startsWith('mselect-')) {
      let _selectedValue:any;
      if (typeof this.controlOptions.items != "undefined") {
        _selectedValue = this.field.Values[0].Value.split(",");
        Object.assign(this.controlOptions, {selectedValue: _selectedValue});
      }
      return this.controlOptions.selectedValue;
    }
    else {
      return this.field.Values[0].Value;
    }
  }

  set controlValue(value:any) {
    // if(this.field.IsMultiLang){
    //   if(typeof value.Lang != "undefined") {
    //     this.currentLang = value.Lang;
    //     return;
    //   }
    //   // this.field.Values.forEach((val, i) => {
    //   //   if(val.Lang == this.currentLang)
    //   //     this.field.Values[i].Value = value;
    //   // })
    // }
  //console.log("DD"+this.field.Type);
    if (this.field.Type == "text" || this.field.Type == "textarea" || this.field.Type == "image") {
      
      this.change.emit(value);
      return;
    }

    if (this.field.Type == "checkbox") {
      this.field.Values.forEach((val, i) => {
        this.field.Values[i].Value = <boolean>value ? "1" : "0";
      })
    }
    else if (this.field.Type.startsWith('mselect-')) {
      this.field.Values.forEach((val, i) => {
        this.field.Values[i].Value = value.join();
      })
    }
    else if (this.field.Type.startsWith('tags-')) {
      //debugger;
      // if(typeof value == "undefined" || value.join() == this.field.Values[0].Value) return;
      // if(this.controlOptions.selectedValue!=value)
      //   this.controlOptions.selectedValue=value;
      let _val = this.tagsValue.map(function (a) {
        return a.value;
      }).join();
      this.field.Values.forEach((val, i) => {
        this.field.Values[i].Value = _val;
      })
    }
    else {
      this.field.Values.forEach((val, i) => {
        if (val.Lang == this.currentLang || typeof this.currentLang == "undefined")
          this.field.Values[i].Value = value;
      })
    }
    //debugger;
    this.change.emit(this.field.Values);
  }

  imageOptions:any = {};

  constructor(private sectionService:SectionService,private fb: FormBuilder) {

  }

  // changeLang(val) {
  //   //this.controlOptions.Lang=val;
  //   this.controlValue={Lang: val};

  // }
  tagsValue = [];

  ngOnInit() {
    if (typeof this.field == "undefined") return;
      if (this.field.Type == "link")
      {
          this.searchTerm.valueChanges
              .debounceTime(400)
              .subscribe(data => {
                  this.sectionService.LoadLinks(data).subscribe(response =>{
                      this.searchResult=response;
                  })
              })
      }
    if (this.field.Type.startsWith("tags-")) {
      if (typeof this.controlOptions.items != "undefined") {
        //debugger;
        let _selectedValue:any;
        if (this.field.Values[0].Value.trim() != "") {
          _selectedValue = _(this.controlOptions.items).keyBy('value')
              .at(this.field.Values[0].Value.split(",")).value();
          _selectedValue = _selectedValue.filter(function(n){ return n != undefined });
          this.controlOptions.selectedValue = _selectedValue;
        }
        else {
          _selectedValue = [];
        }
        this.tagsValue = _selectedValue;
        //Object.assign(this.controlOptions, {selectedValue: _selectedValue});
      }
    }
    // if(this.field.Type == "image") {
    //   this.sectionService.GetImageThumb(this.field.Values[0].Value).subscribe(i => {
    //     Object.assign(this.imageOptions, {
    //         ImageThumb: i.ImageThumb,
    //         NoImageThumb: i.NoImageThumb,
    //         ThumbId: this.field.id + "_thumb",
    //         ImageId: this.field.id + "_image"
    //       });
    //   });
    // }
  }
  SearchTags = (text:string) => {
    if (this.field.Type == "tags-product")
      return this.sectionService.SearchProducts(text);
    else if (this.field.Type == "tags-category")
      return this.sectionService.SearchCategories(text);
    else if (this.field.Type == "tags-brand")
      return this.sectionService.SearchBrands(text);
  }

  ngAfterViewChecked() {
    // if(this.field.Type == "image") {
    //     $('#' + this.field.id + "_image").on('change', (e) => {
    //       console.log('Change made -- ngAfterViewInit');
    //       this.controlValue=e.currentTarget.value;
    //     });
    // }
  }
  //this for image control
  // image_upload() {
  //   $.startImageManager(this.imageOptions.ImageId, this.imageOptions.ThumbId);
  // }
  // clear_image() {
  //   $('#' + this.field.id + "_thumb").attr('src', this.imageOptions.NoImageThumb); 
  //   $('#' + this.field.id + "_image").attr('value', '').trigger('change');
  // }
}
