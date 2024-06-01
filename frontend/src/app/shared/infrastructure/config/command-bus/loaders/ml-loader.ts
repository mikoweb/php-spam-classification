import { inject } from '@angular/core';
import CommandHandlerRegistry from '@app/core/application/command-bus/command-handler-registry';

export default function mlLoader() {
  inject(CommandHandlerRegistry).registerAny([
  ]);
}
