import {Template, Page, Region, Section,UserDataVersion, UserSection} from '../model'
import * as TemplateAct from '../actions/template.act';
import * as _ from "lodash";

export interface State {
  TemplateId: string ,
  UserDataVersionId: string ,
  selectedTemplate: Template ,
  selectedUserDataVersion: UserDataVersion,

  //generated data for the selected page when selectedPage changed<DO NOT PASS IN PAYLOAD>
  selectedPageCodeName: string,
  selectedPage: Page
  //gropedRowRegions: {[RowId: number]: Array<Region>} | {},
  //gropedRegionuserSections: {[RegionId: number]: UserSection} | {} 
};

const initialState: State = {
  TemplateId: "",
  UserDataVersionId: "",
  selectedTemplate: null,
  selectedUserDataVersion: null,

  selectedPageCodeName: "",
  selectedPage: null
};

export function reducer(state = initialState, action: TemplateAct.Actions): State {
  switch (action.type) {
    case TemplateAct.ActionTypes.SET_TEMPLATEID: {
        //console.log("statesssssssssssssssss");
        //console.log(Object.assign({}, state, {TemplateId: action.payload.TemplateId}));
        return Object.assign({}, state, {TemplateId: action.payload.TemplateId});
        // return {
        //     TemplateId: action.payload.TemplateId,
        //     UserDataVersionId: state.UserDataVersionId,
        //     selectedTemplate: state.selectedTemplate,
        //     selectedUserDataVersion: state.selectedUserDataVersion,

        //     selectedPageCodeName: state.selectedPageCodeName,
        //     selectedPage: state.selectedPage
        // };
    }
    case TemplateAct.ActionTypes.LOAD_TEMPLATE: {
        // console.log(state);
        // console.log(action.payload);
        return Object.assign({}, state, {selectedTemplate: action.payload.Template});
        // return {
        //     TemplateId: state.TemplateId,
        //     UserDataVersionId: state.UserDataVersionId,
        //     selectedTemplate: action.payload.Template,
        //     selectedUserDataVersion: state.selectedUserDataVersion,

        //     selectedPageCodeName: state.selectedPageCodeName,
        //     selectedPage: state.selectedPage
        // };  
    }
    case TemplateAct.ActionTypes.SET_USERDATAVERSIONID: {
        return Object.assign({}, state, {UserDataVersionId: action.payload.UserDataVersionId});
        // return {
        //     TemplateId: state.TemplateId,
        //     UserDataVersionId: action.payload.UserDataVersionId,
        //     selectedTemplate: state.selectedTemplate,
        //     selectedUserDataVersion: state.selectedUserDataVersion,

        //     selectedPageCodeName: state.selectedPageCodeName,
        //     selectedPage: state.selectedPage
        // };
    }
    case TemplateAct.ActionTypes.LOAD_USERDATAVERSION: {
        return Object.assign({}, state, {selectedUserDataVersion: action.payload.UserDataVersion});
        // return {
        //     TemplateId: state.TemplateId,
        //     UserDataVersionId: state.UserDataVersionId,
        //     selectedTemplate: state.selectedTemplate,
        //     selectedUserDataVersion: action.payload.UserDataVersion,

        //     selectedPageCodeName: state.selectedPageCodeName,
        //     selectedPage: state.selectedPage
        // };
    }
    case TemplateAct.ActionTypes.SELECT_PAGE: {
        debugger;
        const _selectedPage: Page = _.find(state.selectedTemplate.Pages, {'CodeName': action.payload.selectedPageCodeName});
        //const gropedRowRegions: {[RegionId: number]: UserSection} = _(state.Regions).sortBy('RowOrder').sortBy('ColOrder').groupBy('RegionId');
        //const gropedRegionuserSections: {[RegionId: number]: UserSection} = _(state.UserSections).sortBy('SortOrder').groupBy('RegionId');
        return Object.assign({}, state, {selectedPageCodeName: action.payload.selectedPageCodeName, selectedPage: _selectedPage});
        // return {
        //     TemplateId: state.TemplateId,
        //     UserDataVersionId: state.UserDataVersionId,
        //     selectedTemplate: state.selectedTemplate,
        //     selectedUserDataVersion: state.selectedUserDataVersion,

        //     selectedPageCodeName: action.payload.selectedPageCodeName,
        //     selectedPage: _selectedPage
        // };
    }
    default: {
        return state;
    }
  }
}

//export const getTemplate: Template = (state: State) => state.selectedTemplate;

export const getSelectedPage = (state: State) => { 
    debugger;
    return state.selectedPage;
}

export const getGropedRowRegions: any = (state: State) => {
    debugger;
    return Array.from(_(state.selectedPage.Regions).sortBy('RowOrder').sortBy('ColOrder').groupBy('RowOrder'));
}

//export const gropedRegionUserSections: {[RegionCodeName: string]: UserSection} = (state: State) => _(state.selectedUserDataVersion.UserSections).sortBy('SortOrder').groupBy('RegionCodeName');