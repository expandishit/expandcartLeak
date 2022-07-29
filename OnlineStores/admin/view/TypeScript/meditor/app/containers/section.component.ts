import { Component, Input, OnInit, OnDestroy } from '@angular/core';
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
    @Input("SectionId")
    set SectionId(value: number) {
        if(typeof value == "undefined") return;
        this.id = value;

        this.sectionServiceSub = this.sectionService.LoadSection(this.id).subscribe(
            (_section) => {
                this.objSection = _section ? _section : this.objSection;
            });
        this.sectionService.reqMapData$.subscribe((data) => {
            this.LanguageCode = data ? data.languages1[0].code : null;
        })
        this.commonService.PreviousRoute$.next('common/home');
        this.instantSave = true;
    }
    id: number;
    regionType: string;
    objSection: Section;
    expandedCollectionId: number;
    FieldValChanges: FieldVal[] = [];
    isStylingRegion: boolean = false;
    private LanguageCode: string;
    Languages: any = null;
    currentLanguage: any = null;
    instantSave: boolean = false;

    private routeSub: any;
    private sectionServiceSub: any;
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
            if(typeof params['id'] == "undefined") return;
            this.id = +params['id'];
            this.regionType = params['type'];
            this.isStylingRegion = (params['type'] == "styling");

            this.sectionServiceSub = this.sectionService.LoadSection(this.id).subscribe(
                (_section) => {
                    this.objSection = _section ? _section : this.objSection;
                    if(typeof this.regionType == "undefined") {
                        this.commonService.PreviousRoute$.next("/regionsections/" + this.objSection.RegionId);
                    } else if(this.isStylingRegion) {
                        this.commonService.PreviousRoute$.next("/");
                    } else {
                        this.commonService.PreviousRoute$.next("/specialregion/" + this.regionType);
                    }

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
        // if(this.instantSave) {
        //     this.onSubmit();
        // }
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
        else if(_field.Type == "product-or-category") {
            let _type = "";
            if(_field.Values[0].Value.startsWith("product:")) {
                _type = "tags-product";
            } else if(_field.Values[0].Value.startsWith("category:")) {
                _type = "tags-category";
            }
            else if(_field.Values[0].Value.startsWith("link:")) {
              _type = "tags-link";
            }
            let controlOptions = { items: _.uniq<{ value, display }>(this.sectionService.reqMapData$.getValue()[_type]), selectedValue: [] };
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
        else if (_field.Type == "page") {
            let controlOptions = {};
            Object.assign(controlOptions, { items: this.sectionService.reqMapData$.getValue()["InfoPages"] });
            return controlOptions;
        }
        else if (_field.Type == "language") {
            let controlOptions = {};
            Object.assign(controlOptions, { items: this.Languages});
            return controlOptions;
        }
        return {};
    }

    updateCollectionName(collectionId: number, newCollectionName: string) {
        this.commonService.UpdateObjectName("Collection", collectionId, newCollectionName).subscribe();
    }
}
