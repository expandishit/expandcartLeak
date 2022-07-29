export interface Template {
    Id: string;
    CodeName: string;
    Descriptions: Array<TemplateDescription>;
    Pages: Array<Page>;
}

export interface TemplateDescription {
    Id: string;
    Name: string;
    Description: string;
    Image: string;
    DemoUrl: string;
    Lang: string;
}

export interface Page {
    Id: string;
    CodeName: string;
    Route: string;
    Descriptions: Array<PageDescription>;
    Regions: Array<Region>;
}

export interface PageDescription {
    Id: string;
    Name: string;
    Description: string;
    Lang: string;
}

export interface Region {
    Id: string;
    CodeName: string;
    RowOrder: number;
    ColOrder: number;
    ColWidth: number;
    Sections: Array<Section>;
}

export interface Section {
    Id: string;
    CodeName: string;
    SortOrder: number;
    Descriptions: Array<SectionDescription>;
    Fields: Array<Field>;
    Collections: Array<Collection>;
}

export interface SectionDescription {
    Id: string;
    Name: string;
    Description: string;
    Image: string;
    Thumbnail: string;
    Lang: string;
}

export interface Collection {
    Id: string;
    CodeName: string;
    MaxCollectionsCount: number;
    Descriptions: Array<CollectionDescription>;
    Fields: Array<Field>;
}

export interface CollectionDescription {
    Id: string;
    DefaultName: string;
    DefaultUserCollections: Array<UserCollection>;
    Thumbnail: string;
    Lang: string;
}

export interface Field {
    Id: string;
    CodeName: string;
    Type: string;
    SortOrder: number;
    LookupKey: string;
    IsMultiLang: number;
    Descriptions: Array<FieldDescription>;
}

export interface FieldDescription {
    Id: string;
    Name: string;
    Description: string;
    DefaultUserField: UserField;
    Lang: string;
}

export interface UserDataVersion {
    Id: string;
    Name: string;
    TemplateCodeName: string;
    IsDraft: number;
    IsLive: number;
    Timestamp: Date;
    UserSections: Array<UserSection>;
}

export interface UserSection {
    Id: string;
    Name: string;
    SortOrder: number;
    RegionCodeName: string;
    SectionCodeName: string;
    UserCollections: Array<UserCollection>;
    UserFields: Array<UserField>
}

export interface UserCollection {
    Id: string;
    Name: string;
    SortOrder: number;
    CollectionCodeName: string;
    UserFields: Array<UserField>;
}

export interface UserField {
    Id: string;
    SortOrder: number;
    FieldCodeName: string;
    UserFieldValues: Array<UserFieldValue>;
}
export interface UserFieldValue {
    Id: string;
    Value: string;
    Lang: string;
}

export interface Lookup {
    Id: string;
    Name: string;
    LookupKey: string;
    Value: string;
    SortOrder: number;
}