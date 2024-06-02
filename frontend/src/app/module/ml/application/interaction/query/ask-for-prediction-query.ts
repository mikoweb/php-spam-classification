import { Injectable } from '@angular/core';
import { MlClient } from '@app/module/ml/infrastructure/client/ml-client';
import MessageService from '@app/core/application/message/message-service';
import TranslatorService from '@app/core/application/translator/TranslatorService';

@Injectable({
  providedIn: 'root',
})
export class AskForPredictionQuery {
  constructor(
    private readonly client: MlClient,
    private readonly messageService: MessageService,
    private readonly translator: TranslatorService,
  ) {}

  public async ask(message: string): Promise<boolean> {
    let response: any;

    try {
      response = await this.client.method.post('/ml/predict', {message});
    } catch (error: any) {
      const errorMessage: string = await this.translator.get('ml.predict_fail');
      await this.messageService.createError({message: errorMessage});

      throw error;
    }

    return response.data.isSpam;
  }
}
