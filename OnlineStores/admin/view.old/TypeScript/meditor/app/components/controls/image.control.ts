import { Component, Input, Output, EventEmitter, OnInit, AfterViewChecked } from '@angular/core';
import { Field, FieldVal } from '../../model';
import { SectionService } from '../../services/section.service'
import { CommonService } from '../../services/common.service'
import * as _ from 'lodash'
declare var $: any;
@Component({
  selector: 'ec-image',
  providers: [SectionService],
  templateUrl: './image.control.html',
  styleUrls: ['./image.control.css']
})
export class ImageControl implements OnInit, AfterViewChecked {
  @Input() field: Field;
  @Input() languages: any;
  @Input() ImageThumbs: { Lang: string, ImageThumb: string }[];
  @Input() NoImageThumb: string;
  @Output('update') change: EventEmitter<FieldVal[]> = new EventEmitter<FieldVal[]>();

  currentValue: string;
  currentLang: string;
  values: FieldVal[];
  imageId: string;
  thumbId: string;
  currentImageThumbs: { Lang: string, ImageThumb: string }[];
  currentImageThumb: string;
  noImageThumb: string;

  constructor(private sectionService: SectionService, public commonService: CommonService) { }

  ngOnInit() {
    if (typeof this.field == "undefined" && typeof this.ImageThumbs == "undefined" && typeof this.NoImageThumb == "undefined") return;
    this.values = this.field.Values;
    this.currentValue = this.field.Values[0].Value;

    //Lang
    if (this.field.IsMultiLang == 1) {
      this.currentLang = this.commonService.LanguageObject$.getValue().code;//this.field.Values[0].Lang;
      this.currentValue = _.find<FieldVal>(this.values, (c) => c.Lang == this.currentLang).Value;
      this.commonService.LanguageObject$.subscribe((l) => {if(this.currentLang != l.code) this.changeLang(l.code);});
    }

    //Image Properties
    this.imageId = this.field.id + "_image";
    this.thumbId = this.field.id + "_thumb";
    if (typeof this.ImageThumbs == "undefined" || this.ImageThumbs.length == 0) {
      this.currentImageThumbs = [];
      this.field.Values.forEach((val, i) => {
        this.sectionService.GetImageThumb(val.Value).subscribe((res) => {
          this.currentImageThumbs.push({ Lang: val.Lang, ImageThumb: res.ImageThumb });
          let _imgThumb = _.find<{ Lang: string, ImageThumb: string }>(this.currentImageThumbs, c => c.Lang == this.currentLang);
          if (typeof _imgThumb != "undefined") {
            this.currentImageThumb = _imgThumb.ImageThumb;
            $('#' + this.thumbId).attr('src', this.currentImageThumb);
          }


        });
      });
    }
    let _imgThumb = _.find<{ Lang: string, ImageThumb: string }>(this.ImageThumbs, c => c.Lang == this.currentLang);
    this.currentImageThumb = typeof _imgThumb != "undefined" ? _imgThumb.ImageThumb : this.NoImageThumb;
    this.noImageThumb = this.NoImageThumb;
    this.currentImageThumbs = this.ImageThumbs;

  }

  ngAfterViewChecked() {
    if (this.field.Type == "image") {
      $('#' + this.field.id + "_image").on('change', (e) => {
        this.onImageChange(e.currentTarget.value);
      });
      
    }
  }

  onImageChange(val) {
    this.currentValue = val;
    //this.currentImageThumb=$('#' + this.thumbId).attr('src');


    this.values.forEach((val, i) => {
      if (val.Lang == this.currentLang || this.field.IsMultiLang == 0)
        this.values[i].Value = this.currentValue;
    });

    //save image thumb
    // this.currentImageThumbs.forEach((val, i) => {
    //   if(val.Lang==this.currentLang || this.field.IsMultiLang==0)
    //     this.currentImageThumbs[i].ImageThumb = this.currentImageThumb;
    // });

    this.change.emit(this.values);
  }
  changeLang(lang) {
    //save image thumb
    this.currentImageThumb = $('#' + this.thumbId).attr('src');
    this.currentImageThumbs.forEach((val, i) => {
      if (val.Lang == this.currentLang || this.field.IsMultiLang == 0)
        this.currentImageThumbs[i].ImageThumb = this.currentImageThumb;
    });

    //prepare new thumb
    this.currentLang = lang;
    this.currentValue = _.find<FieldVal>(this.values, (c) => c.Lang == lang).Value;
    this.currentImageThumb = _.find<{ Lang: string, ImageThumb: string }>(this.currentImageThumbs, c => c.Lang == this.currentLang).ImageThumb;
    $('#' + this.thumbId).attr('src', this.currentImageThumb);
    $('#' + this.imageId).attr('value', this.currentValue)
  }
  changeValue(value) {
    this.values.forEach((val, i) => {
      if (val.Lang == this.currentLang || this.field.IsMultiLang == 0)
        this.values[i].Value = value;
    });
    this.change.emit(this.values);
  }
  getLanguageIcon(LangCode: string) {
    let url = window.location.href.slice(0, window.location.href.indexOf("index.php?")) + "view/image/flags/";
    return url + this.languages[LangCode].image;
  }
  getLanguageName(LangCode: string) {
    return this.languages[LangCode].name;
  }
  image_upload() {
    $.startImageManager(this.imageId, this.thumbId);
  }
  clear_image() {
    $('#' + this.thumbId).attr('src', this.noImageThumb);
    $('#' + this.imageId).attr('value', '').trigger('change');
  }
}