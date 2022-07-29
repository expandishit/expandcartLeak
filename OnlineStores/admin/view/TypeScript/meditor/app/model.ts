export interface Template {
    id: number;
    CodeName: string;
    Name: string;
    Description: string;
    Image: string;
    DemoUrl: string;

    Pages: Page[];
}

export interface Page {
    id: number;
    CodeName: string;
    Route: string;
    Name: string;
    Description: string;

    Regions: Region[];
}

export interface Region {
    id: number;
    CodeName: string;
    RowOrder: number;
    ColOrder: number;
    ColWidth: number;
    PageId: number;
    Name: string;
    Description: string;

    UserSections: Section[];
}

export interface Section {
    id: number;
    CodeName: string;
    Name: string;
    Type: string;
    State: string;
    IsCollection: number;
    SortOrder: number;
    RegionId: number;
    DescName: string;
    Description: string;
    Image: string;
    Thumbnail: string;
    CollectionName: string;
    CollectionItemName: string;
    CollectionButtonName: string;

    Fields: Field[];
    Collections: Collection[];
}

export interface Collection {
    id: number;
    Name: string;
    Thumbnail: string;
    IsDefault: number;
    SortOrder: number;
    SectionId: number;

    Fields: Field[];
}

export interface Field {
    id: number;
    CodeName: string;
    Type: string;
    SortOrder: number;
    LookUpKey: string;
    IsMultiLang: number;
    ObjectId: number;
    ObjectType: string;
    Name: string;
    Description: string;

    Values: FieldVal[];
}

export interface FieldVal {
    id: number;
    Value: string;
    Lang: string;
    ObjectFieldId: number;
}

export interface Lookup {
    id: number;
    LookupKey: string;
    Name: string;
    Value: string;
    SortOrder: number;
}

export interface Layout {
    name: string;
    route: string;
}