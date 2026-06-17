const { AppError } = require('./errors');

const ACTIVITY_NAME = 'PermitPaymentConfirmed';
const ROUTING_KEY = 'permit.payment.confirmed';

function isoNow(now) {
  return now().toISOString();
}

function buildAuditLogContent({ teamId, permit, actor, method, occurredAt }) {
  return JSON.stringify(
    {
      team_id: teamId,
      activity_name: ACTIVITY_NAME,
      transaction_type: 'PERMIT_PAYMENT',
      permit_id: permit.id,
      permit_number: permit.permitNumber,
      citizen_email: permit.citizenEmail,
      amount: permit.payment.amount,
      method,
      occurred_at: occurredAt,
      actor: {
        sso_subject: actor.email,
        role: actor.role.role,
      },
    },
    null,
    2,
  );
}

function buildPermitPaymentEvent({ teamId, permit, actor, receiptNumber, occurredAt }) {
  return {
    event: ROUTING_KEY,
    event_name: ROUTING_KEY,
    service_name: 'Digital-City-Permit-Service',
    api_version: 'v1',
    occurred_at: occurredAt,
    team_id: teamId,
    permit: {
      id: permit.id,
      permit_number: permit.permitNumber,
      permit_type: permit.permitType,
      address: permit.address,
      status: 'PAID',
    },
    payment: {
      id: permit.payment.id,
      amount: permit.payment.amount,
      method: permit.payment.method,
      status: 'PAID',
      receipt_number: receiptNumber,
    },
    approved_by: {
      sso_subject: actor.email,
      roles: [actor.role.role],
      permissions: actor.role.permissions,
    },
  };
}

class PaymentService {
  constructor({ database, centralClient, teamId, now = () => new Date() }) {
    this.database = database;
    this.centralClient = centralClient;
    this.teamId = teamId;
    this.now = now;
  }

  listPermits(actor) {
    return this.database.listPermitsByEmail(actor.email);
  }

  async payPermit({ permitId, actor, method = 'VIRTUAL_ACCOUNT' }) {
    if (actor.role.role !== 'WARGA' || !actor.role.permissions.includes('permit:pay')) {
      throw new AppError('Only WARGA users with permit:pay permission can pay permits', 403);
    }

    const permit = this.database.getPermitPayment(permitId, actor.email);
    if (!permit) {
      throw new AppError('Permit not found for authenticated citizen', 404);
    }
    if (!permit.payment) {
      throw new AppError('Permit has no payment invoice', 409);
    }
    if (permit.payment.status === 'PAID') {
      throw new AppError('Permit payment is already paid', 409, permit);
    }

    const occurredAt = isoNow(this.now);
    const logContent = buildAuditLogContent({
      teamId: this.teamId,
      permit,
      actor,
      method,
      occurredAt,
    });

    let audit;
    try {
      audit = await this.centralClient.submitSoapAudit({
        activityName: ACTIVITY_NAME,
        logContent,
      });
      this.database.insertAuditLog({
        paymentId: permit.payment.id,
        teamId: this.teamId,
        activityName: ACTIVITY_NAME,
        requestXml: audit.requestXml,
        responseXml: audit.responseXml,
        receiptNumber: audit.receiptNumber,
        status: 'SUCCESS',
        createdAt: occurredAt,
      });
    } catch (error) {
      this.database.insertAuditLog({
        paymentId: permit.payment.id,
        teamId: this.teamId,
        activityName: ACTIVITY_NAME,
        requestXml: error.details?.requestXml || '',
        responseXml: error.details?.responseXml || null,
        status: 'FAILED',
        error: error.message,
        createdAt: occurredAt,
      });
      throw error;
    }

    this.database.markPaymentPaid(permit.payment.id, {
      method,
      paidAt: occurredAt,
      receiptNumber: audit.receiptNumber,
      createdBy: actor.email,
      publishStatus: 'PENDING',
      updatedAt: occurredAt,
    });

    const event = buildPermitPaymentEvent({
      teamId: this.teamId,
      permit,
      actor,
      receiptNumber: audit.receiptNumber,
      occurredAt,
    });

    try {
      const publish = await this.centralClient.publishEvent({
        routingKey: ROUTING_KEY,
        event,
      });
      this.database.insertPublishedEvent({
        paymentId: permit.payment.id,
        routingKey: ROUTING_KEY,
        event,
        response: publish.response,
        status: 'SUCCESS',
        createdAt: occurredAt,
      });
      this.database.markPaymentPublishStatus(permit.payment.id, 'SUCCESS', occurredAt);

      return {
        permitId: permit.id,
        paymentId: permit.payment.id,
        status: 'PAID',
        receiptNumber: audit.receiptNumber,
        routingKey: ROUTING_KEY,
        audit,
        publish,
        event,
      };
    } catch (error) {
      this.database.insertPublishedEvent({
        paymentId: permit.payment.id,
        routingKey: ROUTING_KEY,
        event,
        status: 'FAILED',
        error: error.message,
        createdAt: occurredAt,
      });
      this.database.markPaymentPublishStatus(permit.payment.id, 'FAILED', occurredAt);
      throw error;
    }
  }
}

module.exports = {
  ACTIVITY_NAME,
  ROUTING_KEY,
  PaymentService,
  buildAuditLogContent,
  buildPermitPaymentEvent,
};
