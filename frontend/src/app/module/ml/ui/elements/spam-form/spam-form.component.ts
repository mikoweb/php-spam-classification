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
import TranslatorService from '@app/core/application/translator/TranslatorService';

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
    private readonly alertController: AlertController,
    private readonly translator: TranslatorService,
  ) {
    super(ele, gsl);
  }

  protected override get useGlobalStyle(): boolean {
    return true;
  }

  protected async onSubmit(): Promise<void> {
    const spam: boolean = await this.askForPredictionQuery.ask(this.form.get('message')?.value);
    let alert;

    const message: string = await this.translator.get(
      spam ? 'ml.message_this_is_spam' : 'ml.message_not_spam'
    );

    if (spam) {
      alert = await this.alertController.create({
        header: await this.translator.get('ml.message_this_is_spam_header'),
        message: `<ion-icon name="alert-circle" class="spam-alert-icon spam-alert-icon--red"></ion-icon>
            <span class="spam-alert spam-alert--red">${message}</span>`,
        buttons: ['OK'],
      });
    } else {
      alert = await this.alertController.create({
        header: await this.translator.get('ml.message_not_spam_header'),
        message: `<ion-icon name="happy" class="spam-alert-icon spam-alert-icon--green"></ion-icon>
            <span class="spam-alert spam-alert--green">${message}</span>`,
        buttons: ['OK'],
      });
    }

    await alert.present();
  }

  protected isFilled(): boolean {
    return this.form.valid;
  }
}
