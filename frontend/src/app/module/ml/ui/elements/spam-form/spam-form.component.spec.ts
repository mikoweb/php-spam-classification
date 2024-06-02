import { ComponentFixture, TestBed, waitForAsync } from '@angular/core/testing';
import { IonicModule } from '@ionic/angular';

import { SpamFormComponent } from './spam-form.component';
import { testProviders } from '@app/core/application/test/test-providers';

describe('SpamFormComponent', () => {
  let component: SpamFormComponent;
  let fixture: ComponentFixture<SpamFormComponent>;

  beforeEach(waitForAsync(() => {
    TestBed.configureTestingModule({
      declarations: [ SpamFormComponent ],
      imports: [IonicModule.forRoot()],
      providers: testProviders,
    }).compileComponents();

    fixture = TestBed.createComponent(SpamFormComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  }));

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
