import { Component, CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { SpamFormComponent } from '@app/module/ml/ui/elements/spam-form/spam-form.component';
import Nl2brPipe from '@app/core/application/pipe/nl2br.pipe';

@Component({
  selector: 'app-default-page',
  templateUrl: './default-page.component.html',
  styleUrls: ['./default-page.component.scss'],
  imports: [
    TranslateModule,
    SpamFormComponent,
    Nl2brPipe,
  ],
  standalone: true,
  schemas: [CUSTOM_ELEMENTS_SCHEMA],
})
export class DefaultPageComponent {
}
