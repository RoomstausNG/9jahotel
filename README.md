
# Angular Interview Preparation Guide

## 1. What is Angular, and why is it used?

Angular is a **TypeScript-based** frontend framework developed by Google for building  **single-page applications (SPAs)** . It provides features like  **two-way data binding, dependency injection, and modular architecture** , making development more structured and scalable.

---

## 2. Difference between Components and Directives in Angular

### Components

Control a section of the UI and have a template, styles, and logic.

```typescript
@Component({
  selector: 'app-hello',
  template: `<h1>Hello, Angular!</h1>`
})
export class HelloComponent { }
```

### Directives

Modify the behavior or appearance of elements.

```typescript
@Directive({
  selector: '[appHighlight]'
})
export class HighlightDirective {
  constructor(el: ElementRef) {
    el.nativeElement.style.backgroundColor = 'yellow';
  }
}
```

---

## 3. Angular Lifecycle Hooks

Lifecycle hooks allow Angular to call methods at different stages of a component’s life.

* `ngOnInit()` → Runs after initialization (used for fetching data).
* `ngOnChanges()` → Runs when input properties change.
* `ngOnDestroy()` → Runs before the component is destroyed (used for cleanup).

Example:

```typescript
export class MyComponent implements OnInit, OnDestroy {
  ngOnInit() { console.log('Component Initialized'); }
  ngOnDestroy() { console.log('Component Destroyed'); }
}
```

---

## 4. How Angular Handles Routing

Angular uses **RouterModule** to manage navigation.

```typescript
const routes: Routes = [
  { path: 'home', component: HomeComponent },
  { path: 'about', component: AboutComponent },
  { path: '**', redirectTo: 'home' }
];
```

Navigation:

```html
<a routerLink="/about">Go to About</a>
<router-outlet></router-outlet>
```

---

## 5. Passing Data Between Components

### Parent → Child using `@Input()`:

```typescript
@Component({ selector: 'child-comp', template: `<p>{{ data }}</p>` })
export class ChildComponent {
  @Input() data: string = '';
}
```

```html
<child-comp [data]="'Hello from Parent'"></child-comp>
```

### Child → Parent using `@Output()` and `EventEmitter`:

```typescript
@Component({ selector: 'child-comp', template: `<button (click)="sendMessage()">Click</button>` })
export class ChildComponent {
  @Output() message = new EventEmitter<string>();
  sendMessage() { this.message.emit('Hello Parent'); }
}
```

```html
<child-comp (message)="receiveMessage($event)"></child-comp>
```

---

## 6. Dependency Injection in Angular

Dependency Injection (DI) allows Angular to provide services automatically.

Service Example:

```typescript
@Injectable({ providedIn: 'root' })
export class DataService {
  getData() { return ['Apple', 'Banana', 'Orange']; }
}
```

Injecting into a Component:

```typescript
export class MyComponent {
  constructor(private dataService: DataService) { }
}
```

---

## 7. Template-driven vs. Reactive Forms

### Template-driven forms

Use `ngModel`, best for simple forms.

```html
<form #form="ngForm">
  <input type="text" name="name" [(ngModel)]="username">
</form>
```

### Reactive Form

Use `FormGroup` and `FormControl` for better control.

```typescript
this.form = new FormGroup({ name: new FormControl('') });
```

```html
<form [formGroup]="form">
  <input formControlName="name">
</form>
```

---

## 8. Making an HTTP Request in Angular

### Import HttpClientModule:

```typescript
import { HttpClientModule } from '@angular/common/http';
@NgModule({ imports: [HttpClientModule] })
```

### Create a service:

```typescript
@Injectable({ providedIn: 'root' })
export class ApiService {
  constructor(private http: HttpClient) { }
  getUsers
```
