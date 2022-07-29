import { Component, OnInit, OnDestroy } from '@angular/core';
import { Section, Field, FieldVal } from '../model'
import { SectionService } from '../services/section.service'
import { CommonService } from '../services/common.service'
import { Router, ActivatedRoute, Params } from '@angular/router';
import { BehaviorSubject } from 'rxjs/BehaviorSubject';
import { FormControl, FormGroup, Validators } from '@angular/forms';
import { DragulaService } from 'ng2-dragula/ng2-dragula'
import * as _ from 'lodash';
declare var $: any;

//import { FormGroup }                 from '@angular/forms';
//import { Control } from '../components/common/control';
//import { ControlRendererComponent } from '../components/control.renderer';
//import { TextboxControl } from '../components/textbox.control';
//import { DropdownControl } from '../components/dropdown.control';


@Component({
    selector: "sectionview",
    providers: [SectionService, DragulaService],
    //declarations: [ControlRendererComponent],
    styleUrls: ['./section.component.css'],
    templateUrl: './section.component.html'
})

export class SectionComponent implements OnInit, OnDestroy {
    id: number;
    regionType: string;
    objSection: Section;
    expandedCollectionId: number;
    FieldValChanges: FieldVal[] = [];
    isStylingRegion: boolean = false;
    private LanguageCode: string;
    Languages: any = null;
    currentLanguage: any = null;
    links:any=null;

    private routeSub: any;
    private sectionServiceSub: any;
    private linksServiceSub: any;
    //private showSaveBtnSub: any;
    //private SaveBtnClickedSub: any;

    constructor(private route: ActivatedRoute, private router: Router, private sectionService: SectionService, private dragulaService: DragulaService, public commonService: CommonService) {
        this.commonService.Languages$.subscribe((langs) => this.Languages = langs);
        this.commonService.LanguageObject$.subscribe((lang) => this.currentLanguage = lang);
        dragulaService.setOptions('collections-bag', {
            moves: function (el, container, handle) {
                return handle.className.indexOf('handle-drag') >= 0;
            }
        });
    }

    ngOnInit() {
        //this.id = +this.route.snapshot.params['id'];
        let group: any = {};
        this.routeSub = this.route.params.subscribe(params => {
            window.scrollTo(0, 0);
            this.id = +params['id'];
            this.regionType = params['type'];
            //debugger;
            //alert(sectionType);
            this.isStylingRegion = (params['type'] == "styling");

            /*this.commonService.TemplateHtml$.subscribe(() => {
                this.scrollToSection(this.id.toString());
            });*/
            //setTimeout(function() {    
            
            //}, 100);
            //$('#frame-viewer').contents().find('body').animate({scrollTop:90},500);
            // let iframe = $('#frame-viewer').contents();
            // debugger;
            // iframe.scrollTo(0, iframe.getElementById('section' + this.id).offsetTop);
            // var childWindow =  document.getElementById("miframe").;
            // childWindow.scrollTo(0,childWindow.getElementById('suchen').offsetTop);
            /*this.commonService.showSaveButton$.next(true);
            this.SaveBtnClickedSub = this.commonService.saveButtonClicked$.subscribe((clicked) => {
                if (clicked) {
                    this.onSubmit();
                    this.commonService.saveButtonClicked$.next(false);
                }
            });*/
            this.sectionServiceSub = this.sectionService.LoadSection(this.id).subscribe(
                (_section) => {
                    //debugger;
                    this.objSection = _section ? _section : this.objSection;
                    //debugger;
                    if(typeof this.regionType == "undefined") {
                        this.commonService.PreviousRoute$.next("/regionsections/" + this.objSection.RegionId);
                    } else if(this.isStylingRegion) {
                        this.commonService.PreviousRoute$.next("/");
                    } else {
                        this.commonService.PreviousRoute$.next("/specialregion/" + this.regionType);
                    }
                    //debugger;
                    //this.LanguageCode$.next(this.sectionService.reqMapDate.languages ? this.sectionService.reqMapDate.languages[0].code : null);
                    this.commonService.HeaderName$.next(this.objSection.DescName);
                    if(this.isStylingRegion && typeof this.objSection != "undefined")
                    {
                        this.expandedCollectionId = this.objSection.Collections[0].id;
                    }
                    //console.log(this.objSection);
                });
            this.sectionService.reqMapData$.subscribe((data) => {
                this.LanguageCode = data ? data.languages1[0].code : null;
            })
        });


    }
    ngOnDestroy() {
        //this.commonService.showSaveButton$.next(false);
        this.routeSub.unsubscribe();
        this.sectionServiceSub.unsubscribe();
        //this.SaveBtnClickedSub.unsubscribe();

    }
    public onSubmit() {
        console.log(this.FieldValChanges);
        this.sectionService.SaveFieldsValChanges(this.FieldValChanges).subscribe((res) => {
            if (res.status == "OK") {
                this.FieldValChanges = [];
                this.reorderCollections();
                console.log(res);
            }
        });

        //subscriber.unsubscribe();
    }

    onControlChange(values: FieldVal[]) {
        //debugger;
        console.log(values);
        values.forEach((val, i) => {
            _.remove<FieldVal>(this.FieldValChanges, (f) => f.id == val.id);
            this.FieldValChanges.push(val);
        });
    }

    addCollection() {
        this.sectionService.AddCollection(this.id).subscribe((_collection) => {
            this.objSection.Collections.push(_collection);
            this.reorderCollections();
        });
    }
    toggleCollection(id: number) {
        this.expandedCollectionId = this.expandedCollectionId == id ? -1 : id;
    }

    reorderCollections() {
        let collectionsOrder: Array<{ CollectionId: number, SortOrder: number }> = [];
        this.objSection.Collections.forEach((collection, i) => collectionsOrder.push({ CollectionId: <number>collection.id, SortOrder: i + 1 }))
        this.sectionService.ReorderCollections(collectionsOrder).subscribe();
    }
    removeCollection(CollectionId: number, index: number) {
        this.sectionService.RemoveCollection(CollectionId).subscribe((res) => {
            if (res.status == "OK") {
                this.objSection.Collections.splice(index, 1); //remove collection from local array
                this.reorderCollections();
            }
        });
    }

    getControlOptions(_field: Field): any {
        if (_field.LookUpKey != "") {
            let controlOptions = {};
            // var _items: {Name: string, Value: string}[] = [];
            // this.sectionService.reqMapDate[field.LookUpKey].forEach(c=> _items.push({Name: c.Name, Value: c.Value}));
            Object.assign(controlOptions, { items: this.sectionService.reqMapData$.getValue()[_field.LookUpKey] });
            return controlOptions;
        }
        else if (_field.Type.startsWith("tags-")) {
            //debugger;
            //let controlOptions = {};
            let controlOptions = { items: _.uniq<{ value, display }>(this.sectionService.reqMapData$.getValue()[_field.Type]), selectedValue: [] };
            return controlOptions;
        }
        else if (_field.Type == "image") {
            let _ImageThumbs = [];
            let _NoImageThumb = this.sectionService.reqMapData$.getValue()['noimagethumb'];
            this.sectionService.reqMapData$.getValue()['images'].forEach((val, i) => {
                if (val.ObjectFieldId == _field.id)
                    _ImageThumbs.push({ Lang: val.Lang, ImageThumb: val.ImageThumb });
            });
            return { ImageThumbs: _ImageThumbs, NoImageThumb: _NoImageThumb };
        }
        return {};
    }

    updateCollectionName(collectionId: number, newCollectionName: string) {
        this.commonService.UpdateObjectName("Collection", collectionId, newCollectionName).subscribe();
    }

    /*scrollToSection(sectionId: string) {
        //debugger;
        if($('#frame-viewer').contents().find('div#section-' + sectionId).length > 0) {
            $('#frame-viewer').contents().scrollTop($('#frame-viewer').contents().find('div#section-' + sectionId).offset().top);
        }
    }*/
}