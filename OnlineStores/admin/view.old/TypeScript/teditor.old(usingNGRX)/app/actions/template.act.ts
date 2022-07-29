import { Action } from '@ngrx/store';
import { Template, UserDataVersion } from '../model';
import { type, cleartype } from '../common/util';

cleartype();
export const ActionTypes = {
  SET_TEMPLATEID:         type('[Template] Set TemplateId'),
  LOAD_TEMPLATE:          type('[Template] Load'),
  SET_USERDATAVERSIONID:  type('[UserDataVersion] Set UserDataVersionId'),
  LOAD_USERDATAVERSION:   type('[UserDataVersion] Load'),
  SELECT_PAGE:            type('[Page] Select Page'),
};

export class SetTemplateIdAction implements Action {
  type = ActionTypes.SET_TEMPLATEID;

  constructor(public payload: {TemplateId: string} | any) { }
}

export class LoadTemplateAction implements Action {
  type = ActionTypes.LOAD_TEMPLATE;

  constructor(public payload: {Template: Template} | any) { }
}

export class SetUserDataVersionIdAction implements Action {
  type = ActionTypes.SET_USERDATAVERSIONID;

  constructor(public payload: {UserDataVersionId: string} | any) { }
}

export class LoadUserDataVersionIdAction implements Action {
  type = ActionTypes.LOAD_USERDATAVERSION;

  constructor(public payload: {UserDataVersion: UserDataVersion} | any) { }
}

export class SelectPageAction implements Action {
  type = ActionTypes.SELECT_PAGE;

  constructor(public payload: {selectedPageCodeName: string} | any) { }
}

export type Actions
  = SetTemplateIdAction
  | LoadTemplateAction
  | SetUserDataVersionIdAction
  | LoadUserDataVersionIdAction
  | SelectPageAction;