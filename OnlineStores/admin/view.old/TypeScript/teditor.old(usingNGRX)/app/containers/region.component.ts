import { Component } from '@angular/core';
import { Page } from '../model'
import { Router, ActivatedRoute, Params } from '@angular/router';

@Component ({
    selector: "regionview",
    template: `
        <div>hello region view {{id}}</div>

    `
})

export class RegionComponent {
    id:number;
    constructor(private route: ActivatedRoute, private router: Router){
        //this.id = +this.route.snapshot.params['id'];
        this.route.params.subscribe(params => {
            this.id = +params['id'];
        });
    }
}