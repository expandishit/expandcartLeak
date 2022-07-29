import { Component } from '@angular/core'

@Component({
  selector: '[gl-rect]',
  inputs: ['rect'],
  template: `
    <svg:rect [attr.x]="rect.x"
                [attr.y]="rect.y"
                [attr.width]="rect.width"
                [attr.height]="rect.height" />
  `
})
export class RectComponent {

}
