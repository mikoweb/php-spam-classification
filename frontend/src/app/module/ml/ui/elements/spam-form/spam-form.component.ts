import { Component, ElementRef } from '@angular/core';
import CustomElementBaseComponent from '@app/core/application/custom-element/custom-element-base-component';
import GlobalStyleLoader from '@app/core/application/custom-element/global-style-loader';
import { CustomElement, customElementParams } from '@app/core/application/custom-element/custom-element';
import { IonicModule } from '@ionic/angular';
import { FormControl, FormGroup, FormsModule, ReactiveFormsModule } from '@angular/forms';
import { TranslateModule } from '@ngx-translate/core';
import { NgForOf } from '@angular/common';
import { AskForPredictionQuery } from '@app/module/ml/application/interaction/query/ask-for-prediction-query';
import { AlertController } from '@ionic/angular';

const { encapsulation, schemas } = customElementParams;

@Component({
  selector: SpamFormComponent.ngSelectorName,
  templateUrl: './spam-form.component.html',
  styleUrls: ['./spam-form.component.scss'],
  standalone: true,
  encapsulation,
  schemas,
  imports: [
    IonicModule,
    ReactiveFormsModule,
    TranslateModule,
    FormsModule,
    NgForOf,
  ],
})
@CustomElement()
export class SpamFormComponent extends CustomElementBaseComponent {
  public static override readonly customElementName: string = 'app-spam-form';
  public static override readonly ngSelectorName: string
    = `${CustomElementBaseComponent.ngPrefix}-${SpamFormComponent.customElementName}`;

  protected formDisabled: boolean = false;

  protected readonly form: FormGroup = new FormGroup({
    message: new FormControl(),
  });

  constructor(
    ele: ElementRef,
    gsl: GlobalStyleLoader,
    private readonly askForPredictionQuery: AskForPredictionQuery,
    private alertController: AlertController
  ) {
    super(ele, gsl);
  }

  protected override get useGlobalStyle(): boolean {
    return true;
  }

  protected async onSubmit(): Promise<void> {
    const spam: boolean = await this.askForPredictionQuery.ask(this.form.get('message')?.value);
    let alert;

    if (spam) {
      alert = await this.alertController.create({
        header: 'To jest spam!',
        message: `<ion-icon name="alert-circle" class="spam-alert-icon spam-alert-icon--red"></ion-icon>
            <span class="spam-alert spam-alert--red">Twoja wiadomość została sklasyfikowana jako spam.</span>`,
        buttons: ['OK'],
      });
    } else {
      alert = await this.alertController.create({
        header: 'Zwyczajna wiadomość',
        message: `<ion-icon name="happy" class="spam-alert-icon spam-alert-icon--green"></ion-icon>
            <span class="spam-alert spam-alert--green">Te nie jest spam.</span>`,
        buttons: ['OK'],
      });
    }

    await alert.present();
  }

  protected isFilled(): boolean {
    return this.form.valid;
  }
}
