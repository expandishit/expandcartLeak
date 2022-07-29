import { Injectable } from '@angular/core';
import { Http, Response } from '@angular/http';
import { Observable } from 'rxjs/Rx';
import { BehaviorSubject } from 'rxjs/BehaviorSubject'
import { Region } from '../model';

@Injectable()
export class RegionService{
    private baseUrl: string = '../ecregion.json';
    private _regions = new BehaviorSubject<Array<Region>>(null);
    regions$ = this._regions.asObservable();

    constructor(private http:Http){
           this.http.get(this.baseUrl).subscribe(response => {
                //console.log('response', response)
                this._regions.next(response.json())
      });
    }

//   getAll(): Observable<Region[]>{
//     let regions$ = this.http
//       .get(`${this.baseUrl}/people`, {headers: this.getHeaders()})
//       .map(mapPersons);
//       return regions$;
//   }

//   get(id: number): Observable<Person> {
//     let person$ = this.http
//       .get(`${this.baseUrl}/people/${id}`, {headers: this.getHeaders()})
//       .map(mapPerson);
//       return person$;
//   }

//   save(person: Person) : Observable<Response>{
//     // this won't actually work because the StarWars API doesn't 
//     // is read-only. But it would look like this:
//     return this.http
//       .put(`${this.baseUrl}/people/${person.id}`, JSON.stringify(person), {headers: this.getHeaders()});
//   }

//   private getHeaders(){
//     let headers = new Headers();
//     headers.append('Accept', 'application/json');
//     return headers;
//   }
}


// function mapPersons(response:Response): Person[]{
//    // The response of the API has a results
//    // property with the actual results
//    return response.json().results.map(toPerson)
// }

// function toPerson(r:any): Person{
//   let person = <Person>({
//     id: extractId(r),
//     url: r.url,
//     name: r.name,
//     weight: r.mass,
//     height: r.height,
//   });
//   console.log('Parsed person:', person);
//   return person;
// }

// // to avoid breaking the rest of our app
// // I extract the id from the person url
// function extractId(personData:any){
//  let extractedId = personData.url.replace('http://swapi.co/api/people/','').replace('/','');
//  return parseInt(extractedId);
// }

// function mapPerson(response:Response): Person{
//   // toPerson looks just like in the previous example
//   return toPerson(response.json());
// }