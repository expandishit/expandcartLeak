import { Component, OnInit, Input } from '@angular/core';
import { Region, Section } from '../model';
import { TemplateService } from '../services/template.service';
import { CommonService } from '../services/common.service';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { BehaviorSubject } from 'rxjs/BehaviorSubject';
import { Observable } from 'rxjs/Observable';
import * as _ from 'lodash'

@Component({
    selector: "specialregionview",
    styleUrls: ['./specialregion.component.css'],
    templateUrl: "./specialregion.component.html"
})

export class SpecialRegionComponent implements OnInit {
    CodeName: string;
    objRegion: Region;
    showEnableSwitch: boolean;

    @Input() set RegionType(value: string) {
        window.scrollTo(0, 0);
        if (typeof value != "undefined") {
            this.CodeName = value;
            this.showEnableSwitch = false;
            this.templateService.isLoaded$.subscribe((loaded) => {
                if (loaded)
                    this.LoadRegion();
            });
        }
    }

    constructor(private route: ActivatedRoute, private router: Router, private templateService: TemplateService, private _router: Router, public commonService: CommonService) {


    }

    ngOnInit() {
        this.route.params.subscribe(params => {
            window.scrollTo(0, 0);
            if (typeof params['codename'] != "undefined") {
                this.CodeName = params['codename'];
                if(this.CodeName != "styling") {
                    this.commonService.PreviousRoute$.next("/");
                }
                this.showEnableSwitch = true;
                this.templateService.isLoaded$.subscribe((loaded) => {
                    if (loaded)
                        this.LoadRegion();
                    else
                        this.templateService.LoadTemplate().subscribe();
                });
            }
        });
    }

    gotoSection(sectionId: number) {
        this._router.navigate(['/section', sectionId, this.CodeName]);
        //this._router.navigate(['/template', {outlets: {'layout-outlet': ['section', sectionId]}}]);
    }

    LoadRegion() {
        if (this.CodeName == "header") {
            this.templateService.HeaderRegion$.subscribe(reg => this.objRegion = reg);
            this.commonService.Res$.subscribe((res) => this.commonService.HeaderName$.next(res._headerSections));
        }
        else if (this.CodeName == "footer") {
            this.templateService.FooterRegion$.subscribe(reg => this.objRegion = reg);
            this.commonService.Res$.subscribe((res) => this.commonService.HeaderName$.next(res._footerSections));
        }
        else if (this.CodeName == "styling") {
            this.templateService.SettingsPage$.subscribe(page => this.objRegion = _.find<Region>(page.Regions, reg => reg.CodeName == "styling"));
        }
    }

    toggleSectionState(SectionId: number, State: any) {
        let _state = State ? "enabled" : "disabled";
        this.templateService.UpdateSectionState(SectionId, _state).subscribe(res => {
            if (res.status == "OK") {
                this.objRegion.UserSections.forEach((section, i) => {
                    if (section.id == SectionId)
                        this.objRegion.UserSections[i].State = _state;
                });
                if (this.CodeName == "header") {
                    this.templateService.HeaderRegion$.next(this.objRegion);
                }
                else if (this.CodeName == "footer") {
                    this.templateService.FooterRegion$.next(this.objRegion);
                }

            }
        })
    }
}